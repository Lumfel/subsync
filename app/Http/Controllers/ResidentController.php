<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Resident;
use App\Models\Announcement;
use App\Models\IssueReport;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ResidentController extends Controller
{
    /** GET /api/residents/me — current resident's own profile */
    public function me()
    {
        $resident = \Illuminate\Support\Facades\Auth::guard('resident')->user();
        if (!$resident) return response()->json(['error' => 'Unauthenticated'], 401);
        return response()->json([
            'id'      => $resident->id,
            'name'    => $resident->name,
            'status'  => $resident->status,
        ]);
    }

    /** GET /api/residents — all residents with household info */
    public function index()
    {
        $residents = Resident::with('household')
            ->select('id', 'house_id', 'name', 'email', 'contact_number', 'status')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'name'            => $r->name,
                'email'           => $r->email,
                'contact_number'  => $r->contact_number,
                'status'          => $r->status,
                'house_id'        => $r->house_id,
                'block_lot'       => $r->household?->block_lot_number ?? '—',
                'household_status'=> $r->household?->status ?? '—',
            ]);

        return response()->json($residents);
    }

    /** POST /api/residents — create resident */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'email'          => 'required|email|unique:residents,email',
            'password'       => 'required|string|min:8',
            'contact_number' => 'nullable|string|max:20',
            'house_id'       => 'nullable|exists:households,id',
            'status'         => 'sometimes|in:Active,Inactive,Non-Compliant',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status']   = $data['status'] ?? 'Active';

        $resident = Resident::create($data);

        return response()->json(['success' => true, 'resident' => $resident]);
    }

    /** PUT /api/residents/{id} — update resident */
    public function update(Request $request, int $id)
    {
        $resident = Resident::findOrFail($id);

        if (auth('resident')->id() && auth('resident')->id() !== $resident->id) {
            return response()->json(['message' => 'You may only update your own resident profile.'], 403);
        }

        $data = $request->validate([
            'name'           => 'sometimes|required|string|max:150',
            'email'          => 'sometimes|required|email|unique:residents,email,' . $id,
            'contact_number' => 'nullable|string|max:20',
            'house_id'       => 'nullable|exists:households,id',
            'status'         => 'sometimes|in:Active,Inactive,Non-Compliant',
            'password'       => 'sometimes|nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $resident->update($data);

        return response()->json(['success' => true, 'resident' => $resident->fresh()]);
    }

    /** DELETE /api/residents/{id} — deactivate */
    public function destroy(int $id)
    {
        $resident = Resident::findOrFail($id);
        $resident->update(['status' => 'Inactive']);

        return response()->json(['success' => true]);
    }

    /** POST /api/residents/{id}/approve — approve pending registration */
    public function approve(int $id)
    {
        $resident = Resident::findOrFail($id);
        $resident->update(['status' => 'Active']);

        return response()->json(['success' => true]);
    }

    /** DELETE /api/residents/{id}/reject — delete a pending registration */
    public function reject(int $id)
    {
        $resident = Resident::findOrFail($id);
        if ($resident->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending registrations can be rejected.'], 422);
        }
        $resident->delete();

        return response()->json(['success' => true]);
    }

    /** GET /api/households — all households */
    public function households()
    {
        $households = Household::with('family:id,family_name,members')
            ->select('id', 'block_lot_number', 'family_id', 'status', 'latitude', 'longitude')
            ->orderBy('block_lot_number')
            ->get();

        return response()->json($households);
    }

    /** POST /api/households — create household */
    public function storeHousehold(Request $request)
    {
        $data = $request->validate([
            'block_lot_number' => 'required|string|max:50|unique:households,block_lot_number',
            'status'           => 'sometimes|in:Active,Inactive,Non-Compliant',
            'family_name'      => 'nullable|string|max:255',
            'members'          => 'nullable|string|max:50',
            'family_head'      => 'nullable|string|max:255',
        ]);

        $data['status'] = $data['status'] ?? 'Active';
        $householdData = \Illuminate\Support\Arr::only($data, ['block_lot_number', 'status']);
        $household = Household::create($householdData);

        if (!empty($data['family_name'])) {
            $family = \App\Models\Family::create([
                'family_name' => $data['family_name'],
                'family_head' => $data['family_head'] ?? null,
                'members'     => $data['members'] ?? null,
            ]);
            $household->update(['family_id' => $family->id]);
        }

        return response()->json(['success' => true, 'household' => $household->load('family:id,family_name,members')]);
    }

    /** PUT /api/households/{id} — update household */
    public function updateHousehold(Request $request, int $id)
    {
        $household = Household::findOrFail($id);

        $data = $request->validate([
            'block_lot_number' => 'sometimes|required|string|max:50|unique:households,block_lot_number,' . $id,
            'status'           => 'sometimes|in:Active,Inactive,Non-Compliant',
            'reason'           => 'nullable|string|max:255',
            'family_name'      => 'nullable|string|max:255',
            'members'          => 'nullable|string|max:50',
        ]);

        $household->update(\Illuminate\Support\Arr::except($data, ['reason', 'family_name', 'members']));

        // Handle family creation or update
        if (isset($data['family_name']) && $data['family_name'] !== '') {
            if ($household->family_id) {
                \App\Models\Family::where('id', $household->family_id)->update([
                    'family_name' => $data['family_name'],
                    'members'     => $data['members'] ?? null,
                ]);
            } else {
                $family = \App\Models\Family::create([
                    'family_name' => $data['family_name'],
                    'members'     => $data['members'] ?? null,
                ]);
                $household->update(['family_id' => $family->id]);
            }
        } elseif ($household->family_id && isset($data['members'])) {
            // Update members count even if family_name not provided
            \App\Models\Family::where('id', $household->family_id)->update(['members' => $data['members']]);
        }

        // Payment-status tracking removed; household status is updated on line 180.

        return response()->json(['success' => true, 'household' => $household->fresh()->load('family:id,family_name,members')]);
    }

    /** DELETE /api/households/{id} */
    public function destroyHousehold(int $id)
    {
        Household::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /** GET /api/household-members?house_id=X */
    public function members(Request $request)
    {
        $query = HouseholdMember::with('household:id,block_lot_number');
        if ($request->query('house_id')) {
            $query->where('house_id', $request->query('house_id'));
        }
        $members = $query->get()->map(fn($m) => [
            'id'             => $m->id,
            'house_id'       => $m->house_id,
            'name'           => $m->name,
            'relationship'   => $m->relationship,
            'contact_number' => $m->contact_number,
            'block_lot'      => $m->household?->block_lot_number ?? '—',
        ]);

        return response()->json($members);
    }

    /** POST /api/household-members */
    public function storeMember(Request $request)
    {
        $data = $request->validate([
            'house_id'       => 'required|exists:households,id',
            'name'           => 'required|string|max:150',
            'relationship'   => 'nullable|string|max:50',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $member = HouseholdMember::create($data);

        return response()->json(['success' => true, 'member' => $member]);
    }

    /** PUT /api/household-members/{id} */
    public function updateMember(Request $request, int $id)
    {
        $member = HouseholdMember::findOrFail($id);
        $data = $request->validate([
            'name'           => 'sometimes|string|max:150',
            'relationship'   => 'nullable|string|max:50',
            'contact_number' => 'nullable|string|max:20',
        ]);
        $member->update($data);
        return response()->json(['success' => true, 'member' => $member]);
    }

    /** DELETE /api/household-members/{id} */
    public function destroyMember(int $id)
    {
        HouseholdMember::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /** GET /api/stats — dashboard counts */
    public function stats()
    {
        return Cache::remember('resident.stats', 60, function () {
            return [
                'total_households'  => Household::count(),
                'total_residents'   => Resident::count(),
                'active_residents'  => Resident::where('status', 'Active')->count(),
            ];
        });
    }

    /** GET /api/resident/portal-data — combined data for resident portal initial load */
    public function portalData(Request $request)
    {
        $residentId = $request->session()->get('resident_id') ?? auth('resident')->id();

        if (!$residentId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json(Cache::remember("resident.portal.data.{$residentId}", 30, function () use ($residentId) {
            $resident = Resident::with('household:id,block_lot_number,status,latitude,longitude')
                ->select('id', 'house_id', 'name', 'email', 'contact_number', 'status')
                ->findOrFail($residentId);

            $household = $resident->household;

            // Household members
            $members = $household ? HouseholdMember::where('house_id', $household->id)
                ->get(['id', 'house_id', 'name', 'relationship', 'contact_number'])
                ->map(fn($m) => [
                    'id'             => $m->id,
                    'house_id'       => $m->house_id,
                    'name'           => $m->name,
                    'role'           => $m->relationship ?? 'Member',
                ])->all() : [];

            // Announcements (recent 10)
            $announcements = Announcement::with(['admin:id,name', 'officer:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn($a) => [
                    'id'         => $a->id,
                    'title'      => $a->title,
                    'content'    => $a->content,
                    'tag'        => $a->tag ?? 'notice',
                    'target'     => $a->target ?? 'All Residents',
                    'priority'   => $a->priority ?? 'Normal',
                    'event_date' => $a->event_date,
                    'posted_by'  => $a->admin?->name ?? $a->officer?->name ?? 'Admin',
                    'created_at' => $a->created_at?->format('M d, Y'),
                ])->all();

            // My issues (recent 20)
            $myIssues = IssueReport::where('resident_id', $residentId)
                ->with('responses', 'media')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(fn($i) => [
                    'id'          => $i->id,
                    'category'    => $i->category,
                    'title'       => $i->title,
                    'description' => $i->description,
                    'status'      => $i->status,
                    'priority'    => $i->priority,
                    'created_at'  => $i->created_at?->format('M d, Y'),
                    'resolved_at' => $i->resolved_at?->format('Y-m-d'),
                    'response'    => $i->responses->first()?->response_content,
                    'media'       => $i->media->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $m->file_name,
                        'url' => \Illuminate\Support\Facades\Storage::url($m->file_path),
                    ])->all(),
                ])->all();

            // My recommendations (recent 20)
            $myRecs = Recommendation::where('resident_id', $residentId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(['id', 'category', 'title', 'description', 'status', 'created_at'])
                ->map(fn($r) => [
                    'id'          => $r->id,
                    'category'    => $r->category,
                    'title'       => $r->title,
                    'description' => $r->description,
                    'status'      => $r->status,
                    'created_at'  => $r->created_at?->format('M d, Y') ?? now()->format('M d, Y'),
                ])->all();

            // HOA expenses (recent 30)
            $expenses = \App\Models\HoaExpense::orderByDesc('created_at')
                ->limit(30)
                ->get()
                ->map(fn($e) => [
                    'id'           => $e->id,
                    'title'        => $e->title,
                    'description'  => $e->description,
                    'amount'       => $e->amount,
                    'expense_date' => $e->expense_date?->format('Y-m-d'),
                    'category'     => $e->category,
                    'status'       => $e->status,
                    'created_at'   => $e->created_at?->format('M d, Y'),
                ])->all();

            // HOA policies
            $policies = \App\Models\HoaPolicy::orderByDesc('created_at')
                ->limit(30)
                ->get()
                ->map(fn($p) => [
                    'id'             => $p->id,
                    'title'          => $p->title,
                    'description'    => $p->description,
                    'category'       => $p->category,
                    'effective_date' => $p->effective_date?->format('Y-m-d'),
                    'status'         => $p->status,
                ])->all();

            return [
                'resident'     => [
                    'id'              => $resident->id,
                    'name'            => $resident->name,
                    'email'           => $resident->email,
                    'contact_number'  => $resident->contact_number,
                    'status'          => $resident->status,
                    'house_id'        => $resident->house_id,
                    'block_lot'       => $household?->block_lot_number ?? '-',
                    'household_status'=> $household?->status ?? '-',
                    'latitude'        => $household?->latitude,
                    'longitude'       => $household?->longitude,
                ],
                'members'        => $members,
                'announcements'  => $announcements,
                'issues'         => $myIssues,
                'recommendations'=> $myRecs,
                'expenses'       => $expenses,
                'policies'       => $policies,
            ];
        }));
    }
}
