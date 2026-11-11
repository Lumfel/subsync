<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Recommendation;
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

        // Rotate the session id on a credential change (session-fixation defence).
        // The response carries the fresh CSRF token so the open page keeps working.
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Password updated.',
            'csrf_token' => csrf_token(),
        ]);
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

    /** GET /api/settings/export — the signed-in actor's personal data (RA 10173 access right). */
    public function exportData()
    {
        $actor = Actor::current();
        $user = $actor['user'];
        if (! $user) {
            abort(401);
        }

        $export = [
            'exported_at' => now()->toIso8601String(),
            'portal' => $actor['type'],
            'profile' => $user->only(['id', 'name', 'email', 'contact_number', 'status', 'created_at']),
        ];

        if ($actor['type'] === 'resident') {
            $house = Household::with('householdMembers')->find($user->house_id);
            $export['household'] = $house ? [
                'block_lot_number' => $house->block_lot_number,
                'members' => $house->householdMembers->map(fn ($m) => [
                    'name' => $m->name,
                    'relationship' => $m->relationship,
                ])->all(),
            ] : null;
            $export['issue_reports'] = IssueReport::where('resident_id', $user->id)
                ->get(['id', 'category', 'title', 'description', 'status', 'priority', 'created_at'])
                ->toArray();
            $export['payment_submissions'] = PaymentSubmission::where('resident_id', $user->id)
                ->get(['id', 'amount', 'reference_no', 'status', 'paid_at', 'created_at'])
                ->toArray();
            $export['recommendations'] = Recommendation::where('resident_id', $user->id)
                ->get(['id', 'title', 'description', 'status', 'created_at'])
                ->toArray();
        }

        if ($actor['type'] === 'officer') {
            $export['announcements'] = Announcement::where('officer_id', $user->id)
                ->get(['id', 'title', 'status', 'created_at'])
                ->toArray();
            $export['sent_files'] = OfficerFile::where('officer_id', $user->id)
                ->get(['id', 'original_name', 'category', 'created_at'])
                ->toArray();
        }

        return response()->json($export, 200, [
            'Content-Disposition' => 'attachment; filename="subsync-my-data.json"',
        ]);
    }

    /** POST /api/settings/erasure-request — file an RA 10173 erasure request with the HOA office. */
    public function requestErasure(Request $request)
    {
        $actor = Actor::current();
        $user = $actor['user'];
        if (! $user) {
            abort(401);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        ActivityLog::create([
            'actor_type' => ucfirst((string) $actor['type']),
            'actor_id' => $user->id,
            'actor_name' => $user->name,
            'action' => 'erasure_request',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'description' => 'Data-erasure request submitted under RA 10173.',
            'meta' => ['reason' => $request->input('reason')],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request received. The HOA office will process it within 15 working days.',
        ]);
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
