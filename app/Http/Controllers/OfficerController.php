<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Support\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OfficerController extends Controller
{
    /** Mutating officer accounts is an administrator-only action. */
    private function ensureAdmin(): void
    {
        if (! session('admin_id')) {
            abort(403, 'Only administrators can manage officer accounts.');
        }
    }

    /** Safe public shape for an officer (never leaks google_id/avatar). */
    private function payload(Officer $o): array
    {
        return $o->only(['id', 'name', 'email', 'contact_number', 'status']);
    }

    /** GET /api/officers */
    public function index()
    {
        return Cache::remember('officers.list', 30, function () {
            return Officer::select('id', 'name', 'email', 'contact_number', 'status')
                ->get()
                ->map(fn ($o) => $o->toArray())
                ->all();
        });
    }

    /** POST /api/officers/avatar — officer updates their own profile photo. */
    public function updateAvatar(Request $request)
    {
        $officer = Auth::guard('officer')->user();
        if (! $officer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate(['avatar' => 'required|image|max:8192']);

        $path = $request->file('avatar')->store('officer-avatars', 'public');
        $url = Storage::url($path);

        $officer->update(['avatar' => $url]);

        return response()->json(['success' => true, 'avatar' => $url]);
    }

    /** POST /api/officers */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:officers,email',
            'password' => 'required|string|min:8',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'Active';

        $officer = Officer::create($data);

        return response()->json(['success' => true, 'officer' => $this->payload($officer)]);
    }

    /** PUT /api/officers/{id} */
    public function update(Request $request, int $id)
    {
        $actor = Actor::current();
        $isSelf = $actor['type'] === 'officer' && (int) $actor['id'] === $id;

        if (! $isSelf) {
            $this->ensureAdmin();
        }

        $officer = Officer::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|unique:officers,email,'.$id,
            'contact_number' => 'nullable|string|max:20',
            'status' => 'sometimes|in:Active,Inactive,Pending',
            'password' => 'sometimes|nullable|string|min:8',
        ]);

        // Officers may correct their own profile (name/contact) but can never
        // self-approve, change status, or reset their password via this route.
        if ($isSelf) {
            $data = array_intersect_key($data, array_flip(['name', 'contact_number']));
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $officer->update($data);

        return response()->json(['success' => true, 'officer' => $this->payload($officer->fresh())]);
    }

    /** DELETE /api/officers/{id} */
    public function destroy(int $id)
    {
        $this->ensureAdmin();

        Officer::findOrFail($id)->update(['status' => 'Inactive']);

        return response()->json(['success' => true]);
    }
}
