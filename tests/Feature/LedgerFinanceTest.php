<?php

namespace Tests\Feature;

use App\Mail\PaymentReminderMail;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\Budget;
use App\Models\FinancialRecord;
use App\Models\HoaExpense;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use App\Models\UserSetting;
use App\Support\Ledger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LedgerFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_balance_reminder_emails_only_residents_who_owe(): void
    {
        Mail::fake();

        $owing = $this->resident();
        $this->due($owing->id, 500);
        Ledger::reconcile($owing->id);

        $settled = $this->resident();
        $this->due($settled->id, 300);
        FinancialRecord::create([
            'resident_id' => $settled->id, 'record_type' => 'Payment', 'description' => 'GCash payment',
            'amount' => 300, 'record_date' => now()->toDateString(),
        ]);
        Ledger::reconcile($settled->id);

        Resident::create([
            'name' => 'Pending P', 'email' => 'pending@example.test',
            'password' => 'Secret@12345', 'status' => 'Pending', 'current_balance' => 100,
        ]);

        $this->artisan('subsync:remind-balances')->assertSuccessful();

        Mail::assertSentCount(1);
        Mail::assertSent(
            PaymentReminderMail::class,
            fn ($m) => $m->hasTo($owing->email) && $m->balance === 500.0
        );
    }

    public function test_balance_reminders_respect_the_email_opt_out(): void
    {
        Mail::fake();

        $resident = $this->resident();
        $this->due($resident->id, 500);
        Ledger::reconcile($resident->id);

        // The resident turned "Email copies" off in Settings.
        UserSetting::resolve('resident', $resident->id)->update(['email_notifications' => false]);

        $this->artisan('subsync:remind-balances')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_balance_reminder_is_a_clean_no_op_when_everyone_is_paid_up(): void
    {
        Mail::fake();

        $this->resident(); // balance 0

        $this->artisan('subsync:remind-balances')->assertSuccessful();

        Mail::assertNothingSent();
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(float $balance = 0): Resident
    {
        return Resident::create([
            'name' => 'R', 'email' => 'r'.uniqid().'@example.test',
            'password' => 'Secret@12345', 'status' => 'Active', 'current_balance' => $balance,
        ]);
    }

    private function due(int $residentId, float $amount, ?string $dueDate = null): FinancialRecord
    {
        return FinancialRecord::create([
            'resident_id' => $residentId, 'record_type' => 'Due', 'description' => 'Dues',
            'amount' => $amount, 'record_date' => now()->toDateString(), 'due_date' => $dueDate, 'source' => 'assessment',
        ]);
    }

    private function pay(int $residentId, float $amount): FinancialRecord
    {
        return FinancialRecord::create([
            'resident_id' => $residentId, 'record_type' => 'Payment', 'description' => 'Payment',
            'amount' => $amount, 'record_date' => now()->toDateString(), 'source' => 'payment',
        ]);
    }

    public function test_balance_is_derived_from_the_ledger(): void
    {
        $r = $this->resident();
        $this->due($r->id, 1000);
        $this->pay($r->id, 400);
        Ledger::reconcile($r->id);

        $res = $this->actingAs($r, 'resident')->getJson('/api/ledger/my')->assertOk();

        $this->assertSame(600.0, (float) $res->json('balance'));
        $this->assertSame(1000.0, (float) $res->json('total_charges'));
        $this->assertSame(400.0, (float) $res->json('total_payments'));
        $this->assertSame(600.0, (float) $r->fresh()->current_balance);
    }

    public function test_overpayment_becomes_a_credit(): void
    {
        $r = $this->resident();
        $this->due($r->id, 500);
        $this->pay($r->id, 700);

        $this->assertSame(-200.0, Ledger::balanceFor($r->id));
    }

    public function test_aging_buckets_outstanding_charges_fifo(): void
    {
        $r = $this->resident();
        $this->due($r->id, 1000, now()->subDays(100)->toDateString());
        $this->pay($r->id, 300);

        $aging = Ledger::agingFor($r->id);

        $this->assertSame(700.0, $aging['buckets']['d90_plus']);
        $this->assertSame(700.0, $aging['total']);
    }

    public function test_dues_generation_is_idempotent(): void
    {
        $r = $this->resident();
        Assessment::create([
            'name' => 'Monthly Dues', 'amount' => 500, 'frequency' => 'monthly',
            'start_date' => now()->startOfYear()->toDateString(), 'is_active' => true,
        ]);

        $this->artisan('assessments:generate-dues')->assertSuccessful();
        $this->artisan('assessments:generate-dues')->assertSuccessful();

        $this->assertSame(1, FinancialRecord::where('resident_id', $r->id)->where('source', 'assessment')->count());
        $this->assertSame(500.0, (float) $r->fresh()->current_balance);
    }

    public function test_transparency_is_period_and_fund_aware(): void
    {
        $r = $this->resident();
        PaymentSubmission::create(['resident_id' => $r->id, 'amount' => 1000, 'method' => 'gcash', 'status' => 'Validated', 'validated_at' => now()]);
        HoaExpense::create(['title' => 'Guard', 'description' => 'x', 'amount' => 300, 'expense_date' => now()->toDateString(), 'category' => 'Security', 'fund' => 'Operating']);
        HoaExpense::create(['title' => 'Roof fund', 'description' => 'x', 'amount' => 200, 'expense_date' => now()->toDateString(), 'category' => 'Reserve', 'fund' => 'Reserve']);
        Budget::create(['category' => 'Security', 'fund' => 'Operating', 'amount' => 500]);

        $res = $this->actingAs($r, 'resident')->getJson('/api/transparency/summary')->assertOk();

        $this->assertSame(1000.0, (float) $res->json('collected'));
        $this->assertSame(300.0, (float) $res->json('operating'));
        $this->assertSame(200.0, (float) $res->json('reserve'));
        $cat = collect($res->json('categories'))->firstWhere('category', 'Security');
        $this->assertSame(300.0, (float) $cat['total']);
        $this->assertSame(500.0, (float) $cat['budget']);
    }

    public function test_admin_can_manage_assessments(): void
    {
        $admin = $this->admin();

        $create = $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/assessments', [
                'name' => 'Dues', 'amount' => 500, 'frequency' => 'monthly', 'start_date' => now()->toDateString(),
            ])
            ->assertOk()->assertJsonPath('success', true);

        $id = $create->json('assessment.id');

        $this->withSession(['admin_id' => $admin->id])
            ->putJson('/api/assessments/'.$id, [
                'name' => 'Dues 2', 'amount' => 600, 'frequency' => 'monthly', 'start_date' => now()->toDateString(),
            ])->assertOk();

        $this->withSession(['admin_id' => $admin->id])->deleteJson('/api/assessments/'.$id)->assertOk();
        $this->assertDatabaseMissing('assessments', ['id' => $id]);
    }

    public function test_aging_endpoint_lists_residents_with_buckets(): void
    {
        $r = $this->resident();
        $this->due($r->id, 1000, now()->subDays(40)->toDateString());

        $res = $this->withSession(['admin_id' => $this->admin()->id])->getJson('/api/ledger/aging')->assertOk();
        $row = collect($res->json())->firstWhere('id', $r->id);

        $this->assertSame(1000.0, (float) $row['buckets']['d31_60']);
    }

    public function test_resident_cannot_read_another_residents_ledger(): void
    {
        $owner = $this->resident();
        $other = $this->resident();

        $this->actingAs($owner, 'resident')
            ->getJson('/api/ledger/'.$other->id)
            ->assertStatus(401); // resident route only exposes /ledger/my
    }

    public function test_finance_endpoints_are_strict_model_safe(): void
    {
        // Dev runs Model::shouldBeStrict(); reproduce the missing-attribute guard.
        Model::preventAccessingMissingAttributes(true);
        try {
            $admin = $this->admin();
            $r = $this->resident();
            $this->due($r->id, 250);
            HoaExpense::create(['title' => 'X', 'description' => 'y', 'amount' => 10, 'expense_date' => now()->toDateString(), 'category' => 'Other', 'fund' => 'Operating']);

            $this->actingAs($r, 'resident')->withHeaders(['Accept' => 'application/json'])->getJson('/api/ledger/my')->assertOk();
            $this->actingAs($r, 'resident')->withHeaders(['Accept' => 'application/json'])->getJson('/api/transparency/summary')->assertOk();

            foreach (['/api/assessments', '/api/budgets', '/api/ledger/aging', '/api/ledger/'.$r->id, '/api/hoa-expenses'] as $path) {
                $this->withSession(['admin_id' => $admin->id])
                    ->withHeaders(['Accept' => 'application/json'])
                    ->getJson($path)
                    ->assertOk();
            }
        } finally {
            Model::preventAccessingMissingAttributes(false);
        }
    }
}
