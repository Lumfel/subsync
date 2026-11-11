<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\FinancialRecord;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use App\Support\Ledger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FlowFixesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        return Resident::firstOrCreate(
            ['email' => 'resident@example.test'],
            ['name' => 'Test Resident', 'password' => 'Secret@12345', 'status' => 'Active', 'current_balance' => 1000]
        );
    }

    public function test_admin_can_approve_a_pending_officer(): void
    {
        $officer = Officer::create([
            'name' => 'Pending Officer', 'email' => 'officer@example.test',
            'password' => 'Secret@12345', 'status' => 'Pending',
        ]);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->putJson('/api/officers/'.$officer->id, ['status' => 'Active'])
            ->assertOk();

        $this->assertSame('Active', $officer->fresh()->status);
    }

    public function test_purge_command_archives_instead_of_deleting(): void
    {
        $issue = IssueReport::create([
            'resident_id' => $this->resident()->id,
            'category' => 'Other',
            'title' => 'Old resolved issue',
            'description' => 'Handled long ago.',
            'status' => 'Resolved',
        ]);

        // Backdate it beyond the 30-day cutoff (bypass model timestamps).
        DB::table('issue_reports')->where('id', $issue->id)
            ->update(['resolved_at' => now()->subDays(31)]);

        $this->artisan('issues:purge-resolved')->assertSuccessful();

        $this->assertDatabaseHas('issue_reports', ['id' => $issue->id]);
        $this->assertNotNull($issue->fresh()->archived_at);
    }

    public function test_validated_payment_reduces_resident_balance(): void
    {
        $resident = $this->resident();
        // Opening charge on the ledger (the balance is derived from the ledger).
        FinancialRecord::create([
            'resident_id' => $resident->id, 'record_type' => 'Due', 'description' => 'Opening balance',
            'amount' => 1000, 'record_date' => now()->toDateString(), 'source' => 'opening',
        ]);
        Ledger::reconcile($resident->id);

        $payment = PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash',
            'status' => 'Pending', 'reference_no' => 'GC999',
        ]);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])
            ->assertOk();

        $this->assertEquals(500.0, (float) $resident->fresh()->current_balance);
    }

    public function test_manual_resident_registration_links_block_lot(): void
    {
        $res = $this->withHeaders(['Accept' => 'application/json'])->post('/register', [
            'role' => 'resident',
            'name' => 'New Resident',
            'email' => 'new@example.test',
            'password' => 'Secret@12345',
            'contact_number' => '09170000000',
            'block_lot' => 'Block 7 Lot 7',
            'consent' => true,
        ]);

        $res->assertOk()->assertJson(['success' => true]);

        $resident = Resident::where('email', 'new@example.test')->first();
        $this->assertNotNull($resident->house_id);
        $this->assertDatabaseHas('households', ['id' => $resident->house_id, 'block_lot_number' => 'Block 7 Lot 7']);
    }

    public function test_household_lookup_is_case_and_whitespace_insensitive(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 3 Lot 12']);

        $a = Household::findOrCreateByBlockLot('block 3  lot 12');
        $b = Household::findOrCreateByBlockLot('BLOCK 3 LOT 12');

        $this->assertSame($house->id, $a->id);
        $this->assertSame($house->id, $b->id);
        $this->assertSame(1, Household::count());
    }

    public function test_manual_officer_registration_creates_pending_officer_and_no_resident(): void
    {
        $res = $this->withHeaders(['Accept' => 'application/json'])->post('/register', [
            'role' => 'officer',
            'name' => 'Maria Santos',
            'email' => 'maria.officer@example.test',
            'contact_number' => '09181234567',
            'password' => 'Secret@12345',
            'consent' => true,
        ]);

        $res->assertOk()->assertJson(['success' => true]);

        // Assert officer was created with Pending status and saved details
        $officer = Officer::where('email', 'maria.officer@example.test')->first();
        $this->assertNotNull($officer, 'Officer record must be created in officers table');
        $this->assertSame('Maria Santos', $officer->name);
        $this->assertSame('09181234567', $officer->contact_number);
        $this->assertSame('Pending', $officer->status);

        // Assert account definitely DID NOT go to residents table
        $this->assertDatabaseMissing('residents', ['email' => 'maria.officer@example.test']);
    }

    public function test_get_register_page_loads_with_registration_tab(): void
    {
        $res = $this->get('/register')->assertOk();
        $res->assertSee('initialTab = "register"', false);
    }
}
