<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Delinquent;
use App\Models\Family;
use App\Models\FinancialRecord;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Recommendation;
use App\Models\Resident;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** GET /api/dashboard-data — single combined payload for the admin SPA */
    public function data()
    {
        // Run all heavy queries together; PHP executes them sequentially but
        // we only pay framework-boot overhead once instead of 11+ times.

        // Residents with household
        $residents = Resident::with('household:id,block_lot_number')
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

        // Households
        $households = Household::select('id', 'block_lot_number', 'status', 'latitude', 'longitude')
            ->orderBy('block_lot_number')
            ->get();

        // Household members
        $hhMembers = HouseholdMember::with('household:id,block_lot_number')
            ->get()
            ->map(fn($m) => [
                'id'             => $m->id,
                'house_id'       => $m->house_id,
                'name'           => $m->name,
                'relationship'   => $m->relationship,
                'contact_number' => $m->contact_number,
                'block_lot'      => $m->household?->block_lot_number ?? '—',
            ]);

        // Delinquents
        $delinquents = Delinquent::with([
            'household:id,block_lot_number,status',
            'household.residents:id,house_id,name,contact_number,current_balance',
        ])->orderByDesc('date_flagged')
          ->get()
          ->map(fn($d) => [
              'id'           => $d->id,
              'house_id'     => $d->house_id,
              'reason'       => $d->reason,
              'date_flagged' => $d->date_flagged,
              'block_lot'    => $d->household?->block_lot_number ?? '—',
              'residents'    => $d->household?->residents->map(fn($r) => [
                  'id'              => $r->id,
                  'name'            => $r->name,
                  'contact_number'  => $r->contact_number,
                  'current_balance' => $r->current_balance,
              ]) ?? [],
          ]);

        // Financial records
        $finRecs = FinancialRecord::with('resident:id,name,house_id', 'resident.household:id,block_lot_number')
            ->orderByDesc('record_date')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'resident_id' => $r->resident_id,
                'resident'    => $r->resident?->name ?? '—',
                'block_lot'   => $r->resident?->household?->block_lot_number ?? '—',
                'record_type' => $r->record_type,
                'description' => $r->description,
                'amount'      => $r->amount,
                'record_date' => $r->record_date,
            ]);

        // Financial summary (aggregated)
        $totalDues     = FinancialRecord::whereIn('record_type', ['Due', 'Penalty'])->sum('amount');
        $totalPayments = FinancialRecord::where('record_type', 'Payment')->sum('amount');
        $totalBalance  = Resident::sum('current_balance');

        $finSummary = [
            'total_collected' => $totalPayments,
            'total_dues'      => $totalDues,
            'total_balance'   => $totalBalance,
            'delinquent_count'=> $delinquents->count(),
        ];

        // Announcements
        $announcements = Announcement::with(['admin:id,name', 'officer:id,name'])
            ->orderByDesc('created_at')
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
            ]);

        // Issues
        $issues = IssueReport::with('resident:id,name,house_id', 'resident.household:id,block_lot_number', 'responses')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'category'    => $i->category,
                'title'       => $i->title,
                'description' => $i->description,
                'status'      => $i->status,
                'priority'    => $i->priority,
                'latitude'    => $i->latitude,
                'longitude'   => $i->longitude,
                'created_at'  => $i->created_at?->format('M d, Y'),
                'resident'    => $i->resident?->name ?? '—',
                'block_lot'   => $i->resident?->household?->block_lot_number ?? '—',
                'responses'   => $i->responses->map(fn($r) => [
                    'id'      => $r->id,
                    'content' => $r->response_content,
                    'type'    => $r->responder_type,
                ]),
            ]);

        // Officers
        $officers = Officer::select('id', 'name', 'email', 'contact_number', 'role_description', 'status')->get();

        // Recommendations
        $recommendations = Recommendation::with('resident:id,name,house_id', 'resident.household:id,block_lot_number')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($rec) => [
                'id'          => $rec->id,
                'resident_id' => $rec->resident_id,
                'resident'    => $rec->resident?->name ?? '—',
                'block_lot'   => $rec->resident?->household?->block_lot_number ?? '—',
                'category'    => $rec->category,
                'title'       => $rec->title,
                'description' => $rec->description,
                'status'      => $rec->status,
                'created_at'  => $rec->created_at?->format('M d, Y'),
            ]);

        // Families
        $families = [];
        try {
            $families = Family::select('id', 'family_name', 'family_head', 'members')
                ->orderBy('family_name')
                ->get();
        } catch (\Throwable $e) {
            // table may not exist
        }

        // Message thread count — admin sees all conversations
        $msgCount = 0;
        try {
            $msgCount = DB::table('conversations')->count();
        } catch (\Throwable $e) {
            // non-critical
        }

        return response()->json([
            'residents'       => $residents,
            'households'      => $households,
            'hhMembers'       => $hhMembers,
            'delinquents'     => $delinquents,
            'finRecs'         => $finRecs,
            'finSummary'      => $finSummary,
            'announcements'   => $announcements,
            'issues'          => $issues,
            'officers'        => $officers,
            'recommendations' => $recommendations,
            'families'        => $families,
            'msgCount'        => $msgCount,
        ]);
    }
}
