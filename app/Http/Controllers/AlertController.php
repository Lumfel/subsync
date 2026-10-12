<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementView;
use App\Models\IssueReport;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $actor = $this->actor();
        $alerts = match ($actor['type']) {
            'admin' => $this->adminAlerts(),
            'officer' => $this->officerAlerts(),
            'resident' => $this->residentAlerts($actor['id']),
            default => [],
        };

        // Respect the user's notification preferences.
        $prefs = \App\Support\Actor::settings($request)->toPrefs();
        $alerts = array_values(array_filter($alerts, function (array $alert) use ($prefs) {
            $id = $alert['id'];
            if (str_starts_with($id, 'announcement') && ! $prefs['notify_announcements']) {
                return false;
            }
            if (str_starts_with($id, 'payment') && ! $prefs['notify_payments']) {
                return false;
            }
            if (str_starts_with($id, 'issue') && ! $prefs['notify_issues']) {
                return false;
            }

            return true;
        }));

        usort($alerts, fn (array $a, array $b) => strcmp($b['created_at'], $a['created_at']));

        return response()->json([
            'alerts' => array_slice($alerts, 0, 20),
            'unread_count' => count(array_filter($alerts, fn (array $alert) => $alert['unread'])),
        ]);
    }

    private function actor(): array
    {
        if (session('admin_id')) {
            return ['type' => 'admin', 'id' => session('admin_id')];
        }

        if (Auth::guard('officer')->check()) {
            return ['type' => 'officer', 'id' => Auth::guard('officer')->id()];
        }

        if (Auth::guard('resident')->check()) {
            return ['type' => 'resident', 'id' => Auth::guard('resident')->id()];
        }

        return ['type' => null, 'id' => null];
    }

    private function adminAlerts(): array
    {
        $alerts = [];
        $pendingResidents = Resident::where('status', 'Pending')->count();
        $urgentIssues = IssueReport::whereIn('priority', ['Critical', 'High'])
            ->where('status', '!=', 'Resolved')->count();
        $openIssues = IssueReport::where('status', '!=', 'Resolved')->count();

        if ($pendingResidents) {
            $alerts[] = $this->alert('pending-residents', 'Pending registrations', "$pendingResidents resident account(s) need review.", 'residents', 'warning', now());
        }

        if ($urgentIssues) {
            $alerts[] = $this->alert('urgent-issues', 'Priority issues require attention', "$urgentIssues high or critical issue(s) remain unresolved.", 'issues', 'critical', now());
        } elseif ($openIssues) {
            $alerts[] = $this->alert('open-issues', 'Open issue reports', "$openIssues issue report(s) are awaiting resolution.", 'issues', 'notice', now());
        }

        $latest = Announcement::where('status', 'Active')->latest('created_at')->first();
        if ($latest) {
            $alerts[] = $this->alert('announcement', 'Latest announcement', $latest->title, 'announcements', 'notice', $latest->created_at);
        }

        return $alerts;
    }

    private function officerAlerts(): array
    {
        $alerts = [];
        $urgentIssues = IssueReport::whereIn('priority', ['Critical', 'High'])
            ->where('status', '!=', 'Resolved')->count();
        $openIssues = IssueReport::where('status', '!=', 'Resolved')->count();

        if ($urgentIssues) {
            $alerts[] = $this->alert('urgent-issues', 'Priority issues require attention', "$urgentIssues high or critical issue(s) remain unresolved.", 'issues', 'critical', now());
        } elseif ($openIssues) {
            $alerts[] = $this->alert('open-issues', 'Open issue reports', "$openIssues issue report(s) are awaiting resolution.", 'issues', 'notice', now());
        }

        $latest = Announcement::where('status', 'Active')->latest('created_at')->first();
        if ($latest) {
            $alerts[] = $this->alert('announcement', 'Latest announcement', $latest->title, 'announcements', 'notice', $latest->created_at);
        }

        return $alerts;
    }

    private function residentAlerts(int $residentId): array
    {
        $alerts = [];
        $unreadAnnouncements = Announcement::where('status', 'Active')
            ->whereDoesntHave('views', fn ($query) => $query->where('resident_id', $residentId))
            ->latest('created_at')->take(10)->get();

        foreach ($unreadAnnouncements as $announcement) {
            $alerts[] = $this->alert(
                "announcement-{$announcement->id}",
                'New announcement',
                $announcement->title,
                'overview',
                $announcement->priority === 'High' ? 'warning' : 'notice',
                $announcement->created_at
            );
        }

        $updatedIssues = IssueReport::where('resident_id', $residentId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->latest('updated_at')->take(5)->get();

        foreach ($updatedIssues as $issue) {
            $alerts[] = $this->alert(
                "issue-{$issue->id}",
                'Issue report updated',
                "{$issue->title} is now {$issue->status}.",
                'issues',
                $issue->priority === 'Critical' ? 'critical' : 'notice',
                $issue->updated_at
            );
        }

        // Payment submitted → admin response (validated / rejected / needs info)
        $reviewedPayments = PaymentSubmission::where('resident_id', $residentId)
            ->whereIn('status', ['Validated', 'Rejected', 'Needs Info'])
            ->where('validated_at', '>=', Carbon::now()->subDays(30))
            ->latest('validated_at')->take(5)->get();

        foreach ($reviewedPayments as $payment) {
            $alerts[] = $this->alert(
                "payment-{$payment->id}",
                'Payment ' . $payment->status,
                'GCash payment of ₱' . number_format((float) $payment->amount, 2) . ' — '
                    . $payment->status . ($payment->rejection_reason ? ': ' . $payment->rejection_reason : '.'),
                'payments',
                $payment->status === 'Validated' ? 'notice' : 'warning',
                $payment->validated_at
            );
        }

        return $alerts;
    }

    private function alert(string $id, string $title, string $message, string $tab, string $severity, mixed $date): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'message' => $message,
            'tab' => $tab,
            'severity' => $severity,
            'unread' => true,
            'created_at' => Carbon::parse($date)->toIso8601String(),
        ];
    }
}
