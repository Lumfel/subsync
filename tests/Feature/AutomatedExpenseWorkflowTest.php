<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ExpenseRequest;
use App\Models\HoaExpense;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutomatedExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Boss',
            'email' => 'admin@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Field Officer Dan',
            'email' => 'officer@example.test',
            'password' => 'Secret@12345',
            'role' => 'Officer',
            'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        $hh = Household::create(['block_lot_number' => 'B1 L2', 'status' => 'Occupied']);

        return Resident::create([
            'house_id' => $hh->id,
            'name' => 'Resident Maria',
            'email' => 'maria@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'exp').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_officer_can_submit_expense_claim_linked_to_an_issue(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $issue = IssueReport::create([
            'resident_id' => $resident->id,
            'title' => 'Broken Streetlight',
            'description' => 'Light flickers at Block 1',
            'category' => 'Maintenance',
            'status' => 'In Progress',
            'assigned_officer_id' => $officer->id,
        ]);

        $res = $this->actingAs($officer, 'officer')
            ->post('/api/expense-requests', [
                'title' => 'LED Light Fixture & Cable',
                'description' => 'Purchased at Ace Hardware for Streetlight fix.',
                'amount' => 1250.50,
                'expense_date' => '2026-11-10',
                'category' => 'Maintenance',
                'fund' => 'Operating',
                'issue_id' => $issue->id,
                'receipt' => $this->pngUpload('receipt.png'),
            ])->assertOk();

        $res->assertJson(['success' => true]);
        $id = $res->json('request.id');

        $this->assertDatabaseHas('expense_requests', [
            'id' => $id,
            'officer_id' => $officer->id,
            'issue_id' => $issue->id,
            'amount' => 1250.50,
            'status' => 'Pending',
        ]);
    }

    public function test_admin_can_approve_officer_claim_into_hoa_expense_ledger(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();

        $claim = ExpenseRequest::create([
            'officer_id' => $officer->id,
            'title' => 'Clubhouse Paint & Brushes',
            'description' => 'Repainted front railings.',
            'amount' => 3400.00,
            'expense_date' => '2026-11-08',
            'category' => 'Amenities',
            'fund' => 'Operating',
            'status' => 'Pending',
        ]);

        $approveRes = $this->withSession(['admin_id' => $admin->id])
            ->actingAs($admin, 'admin')
            ->postJson("/api/expense-requests/{$claim->id}/approve", [
                'admin_notes' => 'Approved and reimbursed via petty cash.',
                'category' => 'Amenities',
                'fund' => 'Operating',
            ])->assertOk();

        $approveRes->assertJson(['success' => true]);
        $hoaExpenseId = $approveRes->json('hoa_expense_id');
        $this->assertNotNull($hoaExpenseId);

        // Verify HoaExpense was created
        $this->assertDatabaseHas('hoa_expenses', [
            'id' => $hoaExpenseId,
            'amount' => 3400.00,
            'category' => 'Amenities',
            'expense_request_id' => $claim->id,
            'status' => 'Approved',
        ]);

        // Verify claim is updated
        $this->assertDatabaseHas('expense_requests', [
            'id' => $claim->id,
            'status' => 'Approved',
            'reviewed_by' => $admin->id,
            'hoa_expense_id' => $hoaExpenseId,
        ]);
    }

    public function test_admin_can_reject_officer_claim_with_notes(): void
    {
        $admin = $this->admin();
        $officer = $this->officer();

        $claim = ExpenseRequest::create([
            'officer_id' => $officer->id,
            'title' => 'Questionable purchase',
            'description' => 'Snacks for patrol.',
            'amount' => 500.00,
            'expense_date' => '2026-11-09',
            'category' => 'Other',
            'fund' => 'Operating',
            'status' => 'Pending',
        ]);

        $res = $this->withSession(['admin_id' => $admin->id])
            ->actingAs($admin, 'admin')
            ->postJson("/api/expense-requests/{$claim->id}/reject", [
                'admin_notes' => 'Snacks are not an authorized HOA reimbursable expense.',
            ])->assertOk();

        $res->assertJson(['success' => true]);

        $this->assertDatabaseHas('expense_requests', [
            'id' => $claim->id,
            'status' => 'Rejected',
            'admin_notes' => 'Snacks are not an authorized HOA reimbursable expense.',
        ]);

        // Ensure NO HoaExpense was generated
        $this->assertDatabaseMissing('hoa_expenses', ['title' => 'Questionable purchase']);
    }

    public function test_issue_lists_include_verified_repair_costs(): void
    {
        $resident = $this->resident();
        $officer = $this->officer();

        $issue = IssueReport::create([
            'resident_id' => $resident->id,
            'title' => 'Main Gate Sensor Repair',
            'description' => 'Vehicle sensor jammed',
            'category' => 'Security',
            'status' => 'Resolved',
        ]);

        HoaExpense::create([
            'title' => 'Replacement Gate Sensor Component',
            'description' => 'Purchased part',
            'amount' => 2800.00,
            'expense_date' => '2026-11-05',
            'category' => 'Security',
            'fund' => 'Operating',
            'status' => 'Approved',
            'issue_id' => $issue->id,
        ]);

        // Resident checks own issues
        $res = $this->actingAs($resident, 'resident')->getJson('/api/issues/my')->assertOk();
        $item = collect($res->json())->firstWhere('id', $issue->id);
        $this->assertNotNull($item);
        $this->assertSame(2800.0, (float) $item['repair_cost']);

        // Staff checks all issues
        $staffRes = $this->actingAs($officer, 'officer')->getJson('/api/issues')->assertOk();
        $staffItem = collect($staffRes->json())->firstWhere('id', $issue->id);
        $this->assertNotNull($staffItem);
        $this->assertSame(2800.0, (float) $staffItem['repair_cost']);
    }

    public function test_monthly_financial_digest_command_publishes_announcement(): void
    {
        $this->admin();
        $resident = $this->resident();

        PaymentSubmission::create([
            'resident_id' => $resident->id,
            'amount' => 5000.00,
            'status' => 'Validated',
            'created_at' => '2026-10-15 10:00:00',
            'validated_at' => '2026-10-15 11:00:00',
        ]);

        HoaExpense::create([
            'title' => 'October Lawn Care',
            'description' => 'Subdivision green spaces mowing',
            'amount' => 2000.00,
            'expense_date' => '2026-10-20',
            'category' => 'Landscaping',
            'fund' => 'Operating',
            'status' => 'Approved',
        ]);

        $this->artisan('subsync:financial-digest', ['--month' => '2026-10', '--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('announcements', [
            'title' => 'Financial Transparency Digest — October 2026',
            'target' => 'All Residents',
            'status' => 'Active',
        ]);
    }
}
