<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Resident;
use App\Models\Delinquent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResidentController extends Controller
{
    /** GET /api/residents — all residents with household info */
    public function index()
    {
        $residents = Resident::with('household')
            ->select('id', 'house_id', 'name', 'email', 'contact_number', 'status', 'current_balance')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'name'            => $r->name,
                'email'           => $r->email,
                'contact_number'  => $r->contact_number,
                'status'          => $r->status,
                'current_balance' => $r->current_balance,
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
            'status'         => 'sometimes|in:Active,Inactive,Delinquent',
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

        $data = $request->validate([
            'name'           => 'sometimes|required|string|max:150',
            'email'          => 'sometimes|required|email|unique:residents,email,' . $id,
            'contact_number' => 'nullable|string|max:20',
            'house_id'       => 'nullable|exists:households,id',
            'status'         => 'sometimes|in:Active,Inactive,Delinquent',
        ]);

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

    /** GET /api/households — all households */
    public function households()
    {
        $households = Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
            ->orderBy('block_lot_number')
            ->get();

        return response()->json($households);
    }

    /** POST /api/households — create household */
    public function storeHousehold(Request $request)
    {
        $data = $request->validate([
            'block_lot_number' => 'required|string|max:50|unique:households,block_lot_number',
            'status'           => 'sometimes|in:Active,Inactive,Delinquent',
        ]);

        $data['status'] = $data['status'] ?? 'Active';
        $household = Household::create($data);

        return response()->json(['success' => true, 'household' => $household]);
    }

    /** PUT /api/households/{id} — update household */
    public function updateHousehold(Request $request, int $id)
    {
        $household = Household::findOrFail($id);

        $data = $request->validate([
            'block_lot_number' => 'sometimes|required|string|max:50|unique:households,block_lot_number,' . $id,
            'status'           => 'sometimes|in:Active,Inactive,Delinquent',
            'reason'           => 'nullable|string|max:255',
        ]);

        $household->update(\Illuminate\Support\Arr::except($data, ['reason']));

        if (($data['status'] ?? null) === 'Delinquent') {
            Delinquent::updateOrCreate(
                ['house_id' => $id],
                [
                    'reason'       => $data['reason'] ?? 'Status marked delinquent',
                    'date_flagged' => now()->toDateString(),
                ]
            );
        } elseif (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive'])) {
            Delinquent::where('house_id', $id)->delete();
        }

        return response()->json(['success' => true, 'household' => $household->fresh()]);
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
        return response()->json([
            'total_households'  => Household::count(),
            'total_residents'   => Resident::count(),
            'active_residents'  => Resident::where('status', 'Active')->count(),
            'delinquent_count'  => Resident::where('status', 'Delinquent')->count(),
        ]);
    }
}
