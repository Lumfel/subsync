<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HoaExpense;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportBulkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(string $name, string $email, string $status = 'Active'): Resident
    {
        return Resident::create(['name' => $name, 'email' => $email, 'password' => 'Secret@12345', 'status' => $status]);
    }

    public function test_residents_csv_export_has_headers_and_rows(): void
    {
        $this->resident('Juan Cruz', 'juan@example.test');

        $res = $this->withSession(['admin_id' => $this->admin()->id])->get('/api/export/residents');

        $res->assertOk();
        $res->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $body = $res->getContent();
        $this->assertStringContainsString('Name,Email', $body);
        $this->assertStringContainsString('Juan Cruz', $body);
    }

    public function test_issues_csv_includes_assignment_columns(): void
    {
        $officer = Officer::create(['name' => 'Officer One', 'email' => 'off@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
        $resident = $this->resident('R', 'r@example.test');
        IssueReport::create([
            'resident_id' => $resident->id, 'category' => 'Road / Pavement', 'title' => 'Pothole',
            'description' => 'x', 'status' => 'Pending', 'assigned_officer_id' => $officer->id,
        ]);

        $res = $this->withSession(['admin_id' => $this->admin()->id])->get('/api/export/issues');
        $body = $res->getContent();

        $this->assertStringContainsString('Assigned Officer', $body);
        $this->assertStringContainsString('Officer One', $body);
    }

    public function test_payments_and_expenses_export(): void
    {
        $resident = $this->resident('R', 'r@example.test');
        PaymentSubmission::create(['resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash', 'reference_no' => 'GC1', 'status' => 'Pending']);
        HoaExpense::create(['title' => 'Landscaping', 'description' => 'x', 'amount' => 1200, 'expense_date' => '2026-09-01', 'category' => 'Maintenance', 'status' => 'Paid']);

        $admin = $this->admin();
        $pay = $this->withSession(['admin_id' => $admin->id])->get('/api/export/payments');
        $exp = $this->withSession(['admin_id' => $admin->id])->get('/api/export/hoa-expenses');

        $this->assertStringContainsString('Reference', $pay->getContent());
        $this->assertStringContainsString('Landscaping', $exp->getContent());
    }

    public function test_bulk_approve_and_reject_residents(): void
    {
        $a = $this->resident('A', 'a@example.test', 'Pending');
        $b = $this->resident('B', 'b@example.test', 'Pending');
        $c = $this->resident('C', 'c@example.test', 'Pending');
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/residents/bulk-approve', ['ids' => [$a->id, $b->id]])
            ->assertOk()->assertJson(['updated' => 2]);

        $this->assertSame('Active', $a->fresh()->status);
        $this->assertSame('Active', $b->fresh()->status);

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/residents/bulk-reject', ['ids' => [$c->id]])
            ->assertOk()->assertJson(['deleted' => 1]);

        $this->assertDatabaseMissing('residents', ['id' => $c->id]);
    }

    public function test_bulk_reject_only_removes_pending(): void
    {
        $active = $this->resident('Active', 'active@example.test', 'Active');

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/residents/bulk-reject', ['ids' => [$active->id]])
            ->assertOk()->assertJson(['deleted' => 0]);

        $this->assertDatabaseHas('residents', ['id' => $active->id]);
    }

    public function test_bulk_archive_issues(): void
    {
        $resident = $this->resident('R', 'r@example.test');
        $i1 = IssueReport::create(['resident_id' => $resident->id, 'category' => 'Utilities', 'title' => 'A', 'description' => 'x', 'status' => 'Pending']);
        $i2 = IssueReport::create(['resident_id' => $resident->id, 'category' => 'Utilities', 'title' => 'B', 'description' => 'x', 'status' => 'Pending']);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->patchJson('/api/issues/bulk-archive', ['ids' => [$i1->id, $i2->id]])
            ->assertOk()->assertJson(['archived' => 2]);

        $this->assertNotNull($i1->fresh()->archived_at);
        $this->assertNotNull($i2->fresh()->archived_at);
    }

    public function test_non_staff_cannot_export(): void
    {
        $resident = $this->resident('R', 'r@example.test');
        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/export/residents')
            ->assertUnauthorized();
    }
}
