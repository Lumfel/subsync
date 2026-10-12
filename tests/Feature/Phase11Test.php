<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HoaExpense;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase11Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(string $email = 'resident@example.test'): Resident
    {
        return Resident::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test Resident', 'password' => 'Secret@12345', 'status' => 'Active']
        );
    }

    private function pngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    }

    private function pngUpload(string $name): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'rcpt').'.png';
        file_put_contents($tmp, $this->pngBytes());

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_resident_can_submit_gcash_payment_with_receipt(): void
    {
        Storage::fake('local');

        $res = $this->actingAs($this->resident(), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/payments', [
                'amount' => 500,
                'reference_no' => 'GC123',
                'gcash_sender_name' => 'Juan Dela Cruz',
                'paid_at' => '2026-10-01',
                'receipt' => $this->pngUpload('receipt.png'),
            ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('payment_submissions', [
            'reference_no' => 'GC123',
            'status' => 'Pending',
        ]);
    }

    public function test_admin_approval_creates_ledger_record(): void
    {
        Storage::fake('local');
        $resident = $this->resident();
        $payment = PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash',
            'reference_no' => 'GC123', 'status' => 'Pending', 'receipt_path' => 'payment-receipts/x.png',
        ]);

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->postJson('/api/payments/'.$payment->id.'/validate', [
                'action' => 'approve',
                'admin_response' => 'Verified against GCash.',
            ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Validated', $payment->fresh()->status);
        $this->assertDatabaseHas('financial_records', [
            'resident_id' => $resident->id,
            'payment_gateway' => 'gcash',
        ]);
    }

    public function test_admin_can_reject_payment_with_reason(): void
    {
        Storage::fake('local');
        $payment = PaymentSubmission::create([
            'resident_id' => $this->resident()->id, 'amount' => 500, 'method' => 'gcash',
            'status' => 'Pending', 'receipt_path' => 'payment-receipts/x.png',
        ]);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->postJson('/api/payments/'.$payment->id.'/validate', [
                'action' => 'reject',
                'rejection_reason' => 'Receipt unclear.',
            ])->assertOk();

        $this->assertSame('Rejected', $payment->fresh()->status);
        $this->assertSame('Receipt unclear.', $payment->fresh()->rejection_reason);
    }

    public function test_resident_cannot_validate_payment(): void
    {
        $payment = PaymentSubmission::create([
            'resident_id' => $this->resident()->id, 'amount' => 500, 'method' => 'gcash',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->resident(), 'resident')
            ->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])
            ->assertStatus(401);

        $this->assertSame('Pending', $payment->fresh()->status);
    }

    public function test_transparency_summary_totals_collected_vs_spent(): void
    {
        $resident = $this->resident();
        PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash', 'status' => 'Validated',
        ]);
        PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 300, 'method' => 'gcash', 'status' => 'Pending',
        ]);
        HoaExpense::create([
            'title' => 'Landscaping', 'description' => 'Gardening', 'amount' => 200,
            'expense_date' => '2026-09-01', 'category' => 'Maintenance',
        ]);

        $res = $this->actingAs($resident, 'resident')
            ->getJson('/api/transparency/summary')
            ->assertOk();

        $this->assertSame(500.0, (float) $res->json('collected'));
        $this->assertSame(200.0, (float) $res->json('spent'));
        $this->assertSame(300.0, (float) $res->json('balance'));
    }

    public function test_resident_cannot_view_another_residents_receipt(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('payment-receipts/x.png', $this->pngBytes());

        $owner = $this->resident('owner@example.test');
        $other = $this->resident('other@example.test');

        $payment = PaymentSubmission::create([
            'resident_id' => $owner->id, 'amount' => 500, 'method' => 'gcash',
            'status' => 'Pending', 'receipt_path' => 'payment-receipts/x.png',
        ]);

        $this->actingAs($owner, 'resident')
            ->get('/api/payments/'.$payment->id.'/receipt')
            ->assertOk();

        $this->actingAs($other, 'resident')
            ->get('/api/payments/'.$payment->id.'/receipt')
            ->assertStatus(403);
    }
}
