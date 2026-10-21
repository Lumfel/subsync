<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /** GET /auth/google — send the user to Google's consent screen. */
    public function redirect(Request $request)
    {
        $role = $request->query('role') === 'officer' ? 'officer' : 'resident';
        $request->session()->put('google_role', $role);

        // Carry the portal across the Google redirect so the callback uses the
        // matching per-portal session cookie.
        Cookie::queue('subsync_portal_hint', $role, 10);

        $this->useCurrentHostForRedirectUri();

        return Socialite::driver('google')->redirect();
    }

    /** GET /auth/google/callback — handle the returned Google account. */
    public function callback(Request $request)
    {
        $role = $request->session()->pull('google_role', 'resident');

        $this->useCurrentHostForRedirectUri();

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect(url('login'))->with('error', 'Google sign-in failed. Please try again.');
        }

        $email = strtolower(trim((string) $google->getEmail()));
        if ($email === '') {
            return redirect(url('login'))->with('error', 'Your Google account has no email address.');
        }

        if ($role === 'officer') {
            $user = Officer::firstWhere('email', $email);
            if (! $user) {
                Officer::create([
                    'name' => $google->getName() ?: 'Officer',
                    'email' => $email,
                    'password' => null,
                    'google_id' => $google->getId(),
                    'avatar' => $google->getAvatar(),
                    'status' => 'Pending',
                ]);

                return redirect(url('login'))->with('message', 'Registration complete. Awaiting admin approval.');
            }
        } else {
            $user = Resident::firstWhere('email', $email);
            if (! $user) {
                $user = Resident::create([
                    'name' => $google->getName() ?: 'Resident',
                    'email' => $email,
                    'password' => null,
                    'google_id' => $google->getId(),
                    'avatar' => $google->getAvatar(),
                    'status' => 'Pending',
                    'current_balance' => 0,
                ]);
            }
        }

        $user->update(['google_id' => $google->getId(), 'avatar' => $google->getAvatar()]);

        if ($role === 'officer') {
            if ($user->status === 'Pending') {
                return redirect(url('login'))->with('error', 'Your account is awaiting admin approval.');
            }

            Auth::guard('resident')->logout();
            $request->session()->forget(['admin_id', 'admin_name', 'resident_id']);
            Auth::guard('officer')->login($user);
            $request->session()->put('active_role', 'officer');
            $request->session()->put('officer_id', $user->id);
            $request->session()->regenerate();

            return redirect(url('officer-portal'));
        }

        // Residents must set their block/lot once (new Google accounts, or accounts without one).
        if (! $user->house_id) {
            $request->session()->put('google_pending_resident_id', $user->id);

            return redirect(url('auth/google/complete'));
        }

        if ($user->status === 'Pending') {
            return redirect(url('login'))->with('error', 'Your account is awaiting admin approval.');
        }

        return $this->loginResident($request, $user);
    }

    /** GET /auth/google/complete — ask a new Google resident for their block/lot. */
    public function showComplete(Request $request)
    {
        $resident = $this->pendingResident($request);
        if (! $resident) {
            return redirect(url('login'));
        }

        return view('layouts.google_complete', [
            'name' => $resident->name,
            'email' => $resident->email,
            'blockLot' => $resident->household?->block_lot_number ?? '',
            'contactNumber' => $resident->contact_number,
            'avatar' => $resident->avatar,
        ]);
    }

    /** POST /auth/google/complete — save the block/lot, then finish sign-in. */
    public function complete(Request $request)
    {
        $resident = $this->pendingResident($request);
        if (! $resident) {
            return redirect(url('login'));
        }

        $data = $request->validate([
            'block_lot' => 'required|string|max:50',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $household = Household::findOrCreateByBlockLot($data['block_lot']);

        $resident->update([
            'house_id' => $household->id,
            'contact_number' => $data['contact_number'] ?? $resident->contact_number,
        ]);

        $request->session()->forget('google_pending_resident_id');

        if ($resident->status === 'Pending') {
            return redirect(url('login'))->with('error', 'Registration complete. Awaiting admin approval.');
        }

        return $this->loginResident($request, $resident);
    }

    /**
     * The OAuth round-trip must return to the host the user started from.
     * A pinned redirect URI (e.g. only the ngrok domain) loses the portal
     * session on the way back, so Socialite finds no "state" and throws
     * InvalidStateException. Take the current request's host instead, so
     * subsync.test and the tunnel both work.
     */
    private function useCurrentHostForRedirectUri(): void
    {
        config(['services.google.redirect' => route('google.callback')]);
    }

    private function pendingResident(Request $request): ?Resident
    {
        $id = $request->session()->get('google_pending_resident_id');

        return $id ? Resident::find($id) : null;
    }

    private function loginResident(Request $request, Resident $user)
    {
        Auth::guard('officer')->logout();
        $request->session()->forget(['admin_id', 'admin_name', 'officer_id']);
        Auth::guard('resident')->login($user);
        $request->session()->put('active_role', 'resident');
        $request->session()->put('resident_id', $user->id);
        $request->session()->regenerate();

        return redirect(url('residents'));
    }
}
