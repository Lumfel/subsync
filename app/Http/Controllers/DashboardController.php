<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Family;
use App\Models\HoaExpense;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Recommendation;
use App\Models\Resident;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** GET /api/dashboard-data — single combined payload for the admin SPA */
    public function data()
    {
        return response()->json(Cache::remember('admin.dashboard.data', 60, function () {
            // Residents with household
            $residents = Resident::with('household:id,block_lot_number,status')
                ->select('id', 'house_id', 'name', 'email', 'contact_number', 'status', 'current_balance')
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'email' => $r->email,
                    'contact_number' => $r->contact_number,
                    'status' => $r->status,
                    'house_id' => $r->house_id,
                    'block_lot' => $r->household?->block_lot_number ?? '-',
                    'household_status' => $r->household?->status ?? '-',
                    'current_balance' => (float) $r->current_balance,
                ])->all();

            // Households
            $households = Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
                ->orderBy('block_lot_number')
                ->get()
                ->map(fn ($h) => $h->toArray())
                ->all();

            // Household members
            $hhMembers = HouseholdMember::with('household:id,block_lot_number')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'house_id' => $m->house_id,
                    'name' => $m->name,
                    'relationship' => $m->relationship,
                    'contact_number' => $m->contact_number,
                    'block_lot' => $m->household?->block_lot_number ?? '-',
                ])->all();

            // Announcements
            $announcements = Announcement::with(['admin:id,name', 'officer:id,name'])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'content' => $a->content,
                    'tag' => $a->tag ?? 'notice',
                    'target' => $a->target ?? 'All Residents',
                    'priority' => $a->priority ?? 'Normal',
                    'event_date' => $a->event_date,
                    'posted_by' => $a->admin?->name ?? $a->officer?->name ?? 'Admin',
                    'created_at' => $a->created_at?->format('M d, Y'),
                ])->all();

            // Issues
            $issues = IssueReport::with('resident:id,name,house_id', 'resident.household:id,block_lot_number', 'responses')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'category' => $i->category,
                    'title' => $i->title,
                    'description' => $i->description,
                    'status' => $i->status,
                    'priority' => $i->priority,
                    'latitude' => $i->latitude,
                    'longitude' => $i->longitude,
                    'created_at' => $i->created_at?->format('M d, Y'),
                    'resolved_at' => $i->resolved_at?->format('Y-m-d'),
                    'resident' => $i->resident?->name ?? '-',
                    'block_lot' => $i->resident?->household?->block_lot_number ?? '-',
                    'responses' => $i->responses->map(fn ($r) => [
                        'id' => $r->id,
                        'content' => $r->response_content,
                        'type' => $r->responder_type,
                    ])->all(),
                ])->all();

            // Officers
            $officers = Officer::select('id', 'name', 'email', 'contact_number', 'status', 'created_at')
                ->get()
                ->map(fn ($o) => $o->toArray())
                ->all();

            // Recommendations
            $recommendations = Recommendation::with('resident:id,name,house_id', 'resident.household:id,block_lot_number')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($rec) => [
                    'id' => $rec->id,
                    'resident_id' => $rec->resident_id,
                    'resident' => $rec->resident?->name ?? '-',
                    'block_lot' => $rec->resident?->household?->block_lot_number ?? '-',
                    'category' => $rec->category,
                    'title' => $rec->title,
                    'description' => $rec->description,
                    'status' => $rec->status,
                    'created_at' => $rec->created_at?->format('M d, Y'),
                ])->all();

            // HOA expenses (for analytics transparency)
            $expenses = HoaExpense::select('id', 'title', 'category', 'amount', 'status', 'expense_date', 'created_at')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'category' => $e->category,
                    'amount' => (float) $e->amount,
                    'status' => $e->status,
                    'expense_date' => $e->expense_date?->format('Y-m-d'),
                    'created_at' => $e->created_at?->format('M d, Y'),
                ])->all();

            // Families
            $families = [];
            try {
                $families = Family::select('id', 'family_name', 'family_head', 'members')
                    ->orderBy('family_name')
                    ->get()
                    ->map(fn ($f) => $f->toArray())
                    ->all();
            } catch (\Throwable $e) {
                // table may not exist
            }

            // Message thread count
            $msgCount = 0;
            try {
                $msgCount = DB::table('conversations')->count();
            } catch (\Throwable $e) {
                // non-critical
            }

            return [
                'residents' => $residents,
                'households' => $households,
                'hhMembers' => $hhMembers,
                'announcements' => $announcements,
                'issues' => $issues,
                'officers' => $officers,
                'recommendations' => $recommendations,
                'families' => $families,
                'expenses' => $expenses,
                'msgCount' => $msgCount,
            ];
        }));
    }
}
