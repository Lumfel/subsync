<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\FinancialRecord;
use App\Models\Household;
use App\Models\IssueAuditLog;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Officer', 'email' => 'officer@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create([
            'name' => 'Resident', 'email' => 'resident@example.test',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);
    }

    private function payment(Resident $resident, string $status = 'Pending'): PaymentSubmission
    {
        return PaymentSubmission::create([
            'resident_id' => $resident->id,
            'amount' => 100,
            'method' => 'gcash',
            'reference_no' => 'REF-'.uniqid(),
            'status' => $status,
        ]);
    }

    public function test_double_approval_never_double_credits_the_ledger(): void
    {
        $admin = $this->admin();
        $resident = $this->resident();
        $payment = $this->payment($resident);

        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        $session()->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])->assertOk();
        $session()->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])->assertOk();

        $this->assertSame(1, FinancialRecord::where('resident_id', $resident->id)
            ->where('record_type', 'Payment')->count(), 'the ledger must be credited exactly once');

        $payment->refresh();
        $this->assertSame('Validated', $payment->status);
        $this->assertNotNull($payment->financial_record_id);
    }

    public function test_validated_payment_cannot_be_rejected(): void
    {
        $admin = $this->admin();
        $resident = $this->resident();
        $payment = $this->payment($resident);

        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        $session()->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])->assertOk();

        $session()->postJson('/api/payments/'.$payment->id.'/validate', [
            'action' => 'reject', 'rejection_reason' => 'oops',
        ])->assertStatus(422);

        $payment->refresh();
        $this->assertSame('Validated', $payment->status);
        $this->assertNull($payment->rejection_reason);
        $this->assertSame(1, FinancialRecord::where('resident_id', $resident->id)->count());
    }

    public function test_rejected_payment_can_still_be_approved(): void
    {
        $admin = $this->admin();
        $resident = $this->resident();
        $payment = $this->payment($resident);

        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        $session()->postJson('/api/payments/'.$payment->id.'/validate', [
            'action' => 'reject', 'rejection_reason' => 'blurry receipt',
        ])->assertOk();

        $session()->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])->assertOk();

        $payment->refresh();
        $this->assertSame('Validated', $payment->status);
        $this->assertSame(1, FinancialRecord::where('resident_id', $resident->id)->count());
    }

    public function test_generate_dues_rejects_a_malformed_period(): void
    {
        $admin = $this->admin();

        // Previously reached the command and blew up on explode('-') with a 500.
        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->postJson('/api/assessments/generate', ['period' => 'nope'])
            ->assertStatus(422);

        $this->artisan('assessments:generate-dues', ['--period' => 'nope'])->assertExitCode(1);
    }

    public function test_bulk_approval_records_activity_and_only_touches_pending(): void
    {
        $admin = $this->admin();
        $pending = Resident::create([
            'name' => 'Pending P', 'email' => 'pending@example.test',
            'password' => 'Secret@12345', 'status' => 'Pending',
        ]);
        $active = Resident::create([
            'name' => 'Active A', 'email' => 'active@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->postJson('/api/residents/bulk-approve', ['ids' => [$pending->id, $active->id]])
            ->assertOk()
            ->assertJson(['updated' => 1]);

        $this->assertSame('Active', $pending->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => 'Resident', 'subject_id' => $pending->id, 'action' => 'updated',
        ]);
    }

    public function test_reassigning_the_same_officer_does_not_log_an_audit_entry(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();
        $resident = $this->resident();

        $issue = IssueReport::create([
            'resident_id' => $resident->id, 'category' => 'Road / Pavement',
            'title' => 'Pothole', 'description' => 'x', 'status' => 'Pending', 'priority' => 'Low',
        ]);

        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin']);

        $session()->putJson('/api/issues/'.$issue->id.'/assign', ['assigned_officer_id' => $officer->id])->assertOk();
        $this->assertSame(1, IssueAuditLog::where('issue_id', $issue->id)->where('action', 'assign')->count());

        // Saving the same assignee again must not create a duplicate audit entry.
        $session()->putJson('/api/issues/'.$issue->id.'/assign', ['assigned_officer_id' => $officer->id])->assertOk();
        $this->assertSame(1, IssueAuditLog::where('issue_id', $issue->id)->where('action', 'assign')->count());

        // A real change still logs.
        $other = Officer::create([
            'name' => 'Other', 'email' => 'other.o@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
        $session()->putJson('/api/issues/'.$issue->id.'/assign', ['assigned_officer_id' => $other->id])->assertOk();
        $this->assertSame(2, IssueAuditLog::where('issue_id', $issue->id)->where('action', 'assign')->count());
    }
}
