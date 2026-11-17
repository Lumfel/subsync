<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HoaExpense;
use App\Models\PaymentSubmission;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTransparencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

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
            ['name' => 'Test Resident', 'password' => 'Secret@12345', 'status' => 'Active']
        );
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'exp').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_admin_can_create_update_and_delete_expense(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $admin = $this->admin();

        $create = $this->withSession(['admin_id' => $admin->id])
            ->post('/api/hoa-expenses', [
                'title' => 'Street lights repair',
                'description' => 'Replaced 5 bulbs along main road.',
                'amount' => 1500,
                'expense_date' => '2026-09-15',
                'category' => 'Maintenance',
                'receipt' => $this->pngUpload('receipt.png'),
            ])->assertOk()->assertJson(['success' => true]);

        $id = $create->json('expense.id');
        $this->assertDatabaseHas('hoa_expenses', ['id' => $id, 'category' => 'Maintenance']);
        $this->assertNotNull($create->json('expense.receipt_url'));

        $this->withSession(['admin_id' => $admin->id])
            ->putJson('/api/hoa-expenses/'.$id, [
                'title' => 'Street lights repair (updated)', 'description' => 'Updated.',
                'amount' => 1800, 'expense_date' => '2026-09-16', 'category' => 'Maintenance',
            ])->assertOk();

        $this->assertDatabaseHas('hoa_expenses', ['id' => $id, 'amount' => 1800]);

        $this->withSession(['admin_id' => $admin->id])
            ->deleteJson('/api/hoa-expenses/'.$id)->assertOk();
        $this->assertDatabaseMissing('hoa_expenses', ['id' => $id]);
    }

    public function test_resident_can_view_expenses_with_receipts(): void
    {
        HoaExpense::create([
            'title' => 'Guardhouse supplies', 'description' => 'Logbooks.',
            'amount' => 300, 'expense_date' => '2026-09-01', 'category' => 'Security',
        ]);

        $res = $this->actingAs($this->resident(), 'resident')->getJson('/api/hoa-expenses')->assertOk();
        $this->assertCount(1, $res->json());
        $this->assertArrayHasKey('receipt_url', $res->json()[0]);
    }

    public function test_resident_can_stream_expense_receipt_but_missing_file_is_404(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $expense = HoaExpense::create([
            'title' => 'Guardhouse supplies', 'description' => 'Logbooks.',
            'amount' => 300, 'expense_date' => '2026-09-01', 'category' => 'Security',
            'receipt_path' => 'uploads/expenses/guardhouse.pdf',
        ]);
        Storage::disk('public')->put('uploads/expenses/guardhouse.pdf', '%PDF-1.4 test');

        $this->actingAs($this->resident(), 'resident')
            ->get('/api/hoa-expenses/'.$expense->id.'/receipt')
            ->assertOk();

        $orphan = HoaExpense::create([
            'title' => 'No receipt', 'description' => 'x',
            'amount' => 100, 'expense_date' => '2026-09-02', 'category' => 'Other',
            'receipt_path' => 'uploads/expenses/missing.pdf',
        ]);

        $this->actingAs($this->resident(), 'resident')
            ->get('/api/hoa-expenses/'.$orphan->id.'/receipt')
            ->assertNotFound();
    }

    public function test_non_admin_cannot_create_expense(): void
    {
        $this->postJson('/api/hoa-expenses', [
            'title' => 'x', 'description' => 'x', 'amount' => 1,
            'expense_date' => '2026-01-01', 'category' => 'Other',
        ])->assertStatus(401);
    }

    public function test_transparency_summary_breaks_down_spending_by_category(): void
    {
        $resident = $this->resident();
        PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 2000, 'method' => 'gcash', 'status' => 'Validated',
        ]);
        HoaExpense::create(['title' => 'A', 'description' => 'x', 'amount' => 500, 'expense_date' => '2026-09-01', 'category' => 'Maintenance']);
        HoaExpense::create(['title' => 'B', 'description' => 'x', 'amount' => 300, 'expense_date' => '2026-09-02', 'category' => 'Security']);

        $res = $this->actingAs($resident, 'resident')->getJson('/api/transparency/summary')->assertOk();

        $this->assertSame(2000.0, (float) $res->json('collected'));
        $this->assertSame(800.0, (float) $res->json('spent'));
        $this->assertSame(1200.0, (float) $res->json('balance'));

        $categories = collect($res->json('categories'))->keyBy('category');
        $this->assertSame(500.0, (float) $categories['Maintenance']['total']);
        $this->assertSame(300.0, (float) $categories['Security']['total']);
    }

    public function test_resident_portal_data_includes_expense_receipt_url(): void
    {
        HoaExpense::create([
            'title' => 'Landscaping',
            'description' => 'Trimming',
            'amount' => 500,
            'expense_date' => '2026-09-01',
            'category' => 'Maintenance',
            'receipt_path' => 'uploads/expenses/landscaping.pdf',
        ]);

        $res = $this->actingAs($this->resident(), 'resident')->getJson('/api/resident/portal-data')->assertOk();

        $this->assertNotEmpty($res->json('expenses'));
        $expense = $res->json('expenses')[0];
        $this->assertTrue($expense['has_receipt']);
        $this->assertStringContainsString('/receipt', $expense['receipt_url']);
    }

    public function test_expense_receipt_can_resolve_with_portal_query(): void
    {
        Storage::fake('public');
        $expense = HoaExpense::create([
            'title' => 'Landscaping',
            'description' => 'Trimming',
            'amount' => 500,
            'expense_date' => '2026-09-01',
            'category' => 'Maintenance',
            'receipt_path' => 'uploads/expenses/landscaping.pdf',
        ]);
        Storage::disk('public')->put('uploads/expenses/landscaping.pdf', '%PDF-1.4 test');

        $this->actingAs($this->resident(), 'resident')
            ->get('/api/hoa-expenses/'.$expense->id.'/receipt?portal=resident')
            ->assertOk();
    }
}
