<?php

namespace Tests\Feature;

use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentReminderMail;
use App\Models\Admin;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentQolTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(string $email = 'resident@example.test'): Resident
    {
        return Resident::create(['name' => 'Juan Cruz', 'email' => $email, 'password' => 'Secret@12345', 'status' => 'Active', 'current_balance' => 1500]);
    }

    private function payment(Resident $resident): PaymentSubmission
    {
        return PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash',
            'reference_no' => 'GC123', 'status' => 'Pending',
        ]);
    }

    public function test_validating_a_payment_emails_a_receipt(): void
    {
        Mail::fake();
        $payment = $this->payment($this->resident());

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/payments/'.$payment->id.'/validate', ['action' => 'approve'])
            ->assertOk();

        Mail::assertSent(PaymentReceiptMail::class, fn ($mail) => $mail->hasTo('resident@example.test'));
    }

    public function test_reminder_email_is_sent_to_resident(): void
    {
        Mail::fake();
        $resident = $this->resident();

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/residents/'.$resident->id.'/remind')
            ->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(PaymentReminderMail::class, fn ($mail) => $mail->hasTo('resident@example.test') && $mail->balance === 1500.0);
    }

    public function test_resident_can_download_own_statement(): void
    {
        $resident = $this->resident();
        $this->payment($resident);

        $res = $this->actingAs($resident, 'resident')
            ->get('/api/residents/'.$resident->id.'/statement');

        $res->assertOk();
        $body = $res->getContent();
        $this->assertStringContainsString('Outstanding balance', $body);
        $this->assertStringContainsString('GC123', $body);
    }

    public function test_staff_can_download_any_statement(): void
    {
        $resident = $this->resident();
        $this->payment($resident);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->get('/api/residents/'.$resident->id.'/statement')
            ->assertOk();
    }

    public function test_resident_cannot_download_another_statement(): void
    {
        $resident = $this->resident();
        $other = $this->resident('other@example.test');

        $this->actingAs($other, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/residents/'.$resident->id.'/statement')
            ->assertForbidden();
    }
}
