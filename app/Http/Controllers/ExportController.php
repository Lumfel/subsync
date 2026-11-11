<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HoaExpense;
use App\Models\IssueReport;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use App\Support\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    private function authorizeStaff(): void
    {
        if (! session('admin_id') && ! auth('officer')->check()) {
            abort(403, 'Only administrators or officers may export data.');
        }
    }

    /** Record bulk-PII exports in the activity trail (RA 10173 accountability). */
    private function logExport(string $what, int $count): void
    {
        $actor = Actor::current();

        ActivityLog::create([
            'actor_type' => ucfirst((string) $actor['type']),
            'actor_id' => $actor['id'],
            'actor_name' => $actor['name'],
            'action' => 'export',
            'subject_type' => $what,
            'subject_id' => null,
            'description' => "Exported {$what} as CSV ({$count} row(s)).",
            'meta' => ['rows' => $count],
        ]);
    }

    /** Neutralise spreadsheet formula injection (leading = + - @ or control chars). */
    private function safeCsvCell($value): string
    {
        $v = (string) ($value ?? '');
        if ($v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$v;
        }

        return $v;
    }

    /** Stream an array of rows as a CSV download. */
    private function csv(string $filename, array $headers, iterable $rows)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, array_map([$this, 'safeCsvCell'], $row));
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** GET /api/export/residents */
    public function residents()
    {
        $this->authorizeStaff();

        $rows = Resident::with('household:id,block_lot_number')->orderBy('name')->get()->map(fn ($r) => [
            $r->id, $r->name, $r->email, $r->contact_number,
            $r->household?->block_lot_number, $r->status,
            number_format((float) $r->current_balance, 2, '.', ''),
        ]);

        $this->logExport('resident directory', $rows->count());

        return $this->csv('residents-'.date('Ymd').'.csv', ['ID', 'Name', 'Email', 'Contact', 'Block/Lot', 'Status', 'Balance'], $rows);
    }

    /** GET /api/export/issues */
    public function issues()
    {
        $this->authorizeStaff();

        $rows = IssueReport::with('resident:id,name', 'assignee:id,name')->orderByDesc('created_at')->get()->map(fn ($i) => [
            $i->id, $i->title, $i->category, $i->status, $i->priority,
            $i->resident?->name, $i->assignee?->name,
            $i->due_date?->format('Y-m-d'), $i->created_at?->format('Y-m-d'),
        ]);

        $this->logExport('issue reports', $rows->count());

        return $this->csv('issues-'.date('Ymd').'.csv', ['ID', 'Title', 'Category', 'Status', 'Priority', 'Resident', 'Assigned Officer', 'Due Date', 'Created'], $rows);
    }

    /** GET /api/export/payments */
    public function payments()
    {
        $this->authorizeStaff();

        $rows = PaymentSubmission::with('resident:id,name')->orderByDesc('created_at')->get()->map(fn ($p) => [
            $p->id, $p->resident?->name, number_format((float) $p->amount, 2, '.', ''),
            $p->method, $p->reference_no, $p->status,
            $p->paid_at?->format('Y-m-d'), $p->validated_at?->format('Y-m-d H:i'),
        ]);

        $this->logExport('payment submissions', $rows->count());

        return $this->csv('payments-'.date('Ymd').'.csv', ['ID', 'Resident', 'Amount', 'Method', 'Reference', 'Status', 'Paid At', 'Validated At'], $rows);
    }

    /** GET /api/export/hoa-expenses */
    public function hoaExpenses()
    {
        $this->authorizeStaff();

        $rows = HoaExpense::orderByDesc('expense_date')->get()->map(fn ($e) => [
            $e->id, $e->title, $e->category, number_format((float) $e->amount, 2, '.', ''),
            $e->expense_date?->format('Y-m-d'), $e->status,
        ]);

        $this->logExport('HOA expenses', $rows->count());

        return $this->csv('hoa-expenses-'.date('Ymd').'.csv', ['ID', 'Title', 'Category', 'Amount', 'Date', 'Status'], $rows);
    }

    /** GET /api/residents/{id}/statement — payment statement (owner resident or staff). */
    public function residentStatement(Request $request, int $id)
    {
        $isStaff = session('admin_id') || Auth::guard('officer')->check();
        $ownerId = Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
        $isOwner = $ownerId !== null && (int) $ownerId === $id;

        if (! $isStaff && ! $isOwner) {
            abort(403);
        }

        $resident = Resident::findOrFail($id);

        $rows = PaymentSubmission::where('resident_id', $id)->orderBy('created_at')->get()->map(fn ($p) => [
            $p->id, $p->paid_at?->format('Y-m-d'), number_format((float) $p->amount, 2, '.', ''),
            $p->reference_no, $p->status,
        ])->values()->all();

        $rows[] = ['', '', 'Outstanding balance', number_format((float) $resident->current_balance, 2, '.', ''), ''];

        $this->logExport('resident statement #'.$resident->id, count($rows));

        return $this->csv('statement-'.$resident->id.'-'.date('Ymd').'.csv', ['ID', 'Date Paid', 'Amount', 'Reference', 'Status'], $rows);
    }
}
