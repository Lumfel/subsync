<?php

namespace App\Http\Controllers;

use App\Support\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /** Map actor type → DB table. */
    private const TABLES = [
        'admin' => 'admins',
        'officer' => 'officers',
        'resident' => 'residents',
    ];

    /** GET /settings — the standalone settings page (all roles). */
    public function page()
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return redirect('/login');
        }

        return view('settings', [
            'actor' => $actor,
            'prefs' => Actor::settings()->toPrefs(),
            'portalUrl' => match ($actor['type']) {
                'admin' => '/dashboard',
                'officer' => '/officer-portal',
                'resident' => '/residents',
                default => '/',
            },
        ]);
    }

    /** GET /api/settings — current profile + preferences. */
    public function show()
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => $this->payload($actor),
            'preferences' => Actor::settings()->toPrefs(),
        ]);
    }

    /** PUT /api/settings/profile — name, email, contact number. */
    public function updateProfile(Request $request)
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique(self::TABLES[$actor['type']], 'email')->ignore($actor['id'])],
            'contact_number' => 'nullable|string|max:20',
        ]);

        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // The admins table has no contact_number column — writing it would be
        // silently discarded, which strict mode turns into a 500.
        if ($actor['type'] !== 'admin') {
            $fields['contact_number'] = $data['contact_number'] ?? null;
        }

        $actor['user']->update($fields);

        // Keep the admin display name in sync with the session.
        if ($actor['type'] === 'admin') {
            $request->session()->put('admin_name', $data['name']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'user' => $this->payload(Actor::current()),
        ]);
    }

    /** POST /api/settings/password — change password. */
    public function updatePassword(Request $request)
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $actor['user'];
        $rules = ['password' => 'required|string|min:8|confirmed'];
        if ($user->password) {
            $rules['current_password'] = 'required|string';
        }

        $data = $request->validate($rules);

        if ($user->password && ! Hash::check($data['current_password'] ?? '', $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current password is incorrect.',
                'errors' => ['current_password' => ['Your current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => $data['password']]);

        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }

    /** POST /api/settings/photo — upload a profile photo. */
    public function updatePhoto(Request $request)
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate(['photo' => 'required|image|max:8192']);

        $path = $request->file('photo')->store('avatars', 'public');
        $url = Storage::url($path);

        $actor['user']->update(['avatar' => $url]);

        return response()->json(['success' => true, 'avatar' => $url]);
    }

    /** PUT /api/settings/preferences — appearance + notification preferences. */
    public function updatePreferences(Request $request)
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'theme' => 'required|in:light,dark,system',
            'density' => 'required|in:comfortable,compact',
            'font_scale' => 'required|in:small,default,large',
            'notify_announcements' => 'boolean',
            'notify_payments' => 'boolean',
            'notify_issues' => 'boolean',
            'notify_messages' => 'boolean',
            'email_notifications' => 'boolean',
        ]);

        $settings = Actor::settings();
        $settings->update($data);

        return response()->json(['success' => true, 'preferences' => $settings->fresh()->toPrefs()]);
    }

    private function payload(array $actor): array
    {
        $u = $actor['user'] ?? null;

        return [
            'type' => $actor['type'],
            'id' => $u?->id,
            'name' => $u?->name,
            'email' => $u?->email,
            // Only residents/officers have a contact number; reading it from the
            // admin model violates strict mode (missing attribute) → 500.
            'contact_number' => $actor['type'] === 'admin' ? null : $u?->contact_number,
            'avatar' => $u?->avatar,
        ];
    }
}
