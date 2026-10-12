<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\HoaExpense;
use App\Models\HoaPolicy;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use App\Support\Actor;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** GET /api/search?q= — role-aware global search. */
    public function index(Request $request)
    {
        $actor = Actor::current();
        if (! $actor['type']) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $raw = trim((string) $request->query('q', ''));
        if ($raw === '') {
            return response()->json(['query' => $raw, 'results' => []]);
        }

        // Strip LIKE wildcards so a query of "%" or "_" can't enumerate every row.
        $q = str_replace(['%', '_'], '', $raw);
        if ($q === '') {
            return response()->json(['query' => $raw, 'results' => []]);
        }

        $like = '%'.$q.'%';
        $results = [];

        if (in_array($actor['type'], ['admin', 'officer'], true)) {
            $this->staffResults($results, $like, $actor['type']);
        } else {
            $this->residentResults($results, $like, (int) $actor['id']);
        }

        return response()->json(['query' => $raw, 'results' => array_slice($results, 0, 40)]);
    }

    private function staffResults(array &$results, string $like, string $type): void
    {
        if ($type === 'admin') {
            $residents = Resident::query()
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('contact_number', 'like', $like))
                ->limit(8)->get(['id', 'name', 'email', 'status']);
            foreach ($residents as $r) {
                $results[] = $this->item('Resident', $r->name, trim($r->email.' · '.$r->status, ' ·'), 'residents');
            }

            foreach (Household::query()->where('block_lot_number', 'like', $like)->limit(6)->get(['id', 'block_lot_number', 'status']) as $h) {
                $results[] = $this->item('Household', $h->block_lot_number, $h->status, 'residents');
            }

            $officers = Officer::query()
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->limit(6)->get(['id', 'name', 'email']);
            foreach ($officers as $o) {
                $results[] = $this->item('Officer', $o->name, $o->email, 'officers');
            }
        }

        $issues = IssueReport::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('category', 'like', $like)->orWhere('description', 'like', $like))
            ->limit(8)->get(['id', 'title', 'category', 'status']);
        foreach ($issues as $i) {
            $results[] = $this->item('Issue', $i->title, trim($i->category.' · '.$i->status, ' ·'), $type === 'officer' ? 'reports' : 'issues');
        }

        $announcements = Announcement::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('content', 'like', $like))
            ->limit(6)->get(['id', 'title', 'tag']);
        foreach ($announcements as $a) {
            $results[] = $this->item('Announcement', $a->title, $a->tag, 'announcements');
        }

        $policies = HoaPolicy::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->limit(6)->get(['id', 'title', 'status']);
        foreach ($policies as $p) {
            $results[] = $this->item('Policy', $p->title, $p->status, 'policies');
        }
    }

    private function residentResults(array &$results, string $like, int $residentId): void
    {
        $announcements = Announcement::query()
            ->where('status', 'Active')
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('content', 'like', $like))
            ->limit(6)->get(['id', 'title', 'tag']);
        foreach ($announcements as $a) {
            $results[] = $this->item('Announcement', $a->title, $a->tag, 'overview');
        }

        $issues = IssueReport::query()
            ->where('resident_id', $residentId)
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('category', 'like', $like))
            ->limit(6)->get(['id', 'title', 'category', 'status']);
        foreach ($issues as $i) {
            $results[] = $this->item('My concern', $i->title, trim($i->category.' · '.$i->status, ' ·'), 'issues');
        }

        $policies = HoaPolicy::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->limit(6)->get(['id', 'title', 'status']);
        foreach ($policies as $p) {
            $results[] = $this->item('Policy', $p->title, $p->status, 'policies');
        }

        $expenses = HoaExpense::query()
            ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('category', 'like', $like)->orWhere('description', 'like', $like))
            ->limit(6)->get(['id', 'title', 'amount']);
        foreach ($expenses as $e) {
            $results[] = $this->item('HOA expense', $e->title, '₱'.number_format((float) $e->amount, 2), 'expenses');
        }

        $payments = PaymentSubmission::query()
            ->where('resident_id', $residentId)
            ->where('reference_no', 'like', $like)
            ->limit(6)->get(['id', 'reference_no', 'status']);
        foreach ($payments as $p) {
            $results[] = $this->item('Payment', $p->reference_no ?: 'Payment', $p->status, 'payments');
        }
    }

    private function item(string $tag, string $label, string $sub, string $go): array
    {
        return ['tag' => $tag, 'label' => $label, 'sub' => $sub, 'go' => $go];
    }
}
