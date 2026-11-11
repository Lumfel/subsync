<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HoaExpense;
use App\Models\Household;
use App\Models\Media;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateMediaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function officer(string $email = 'officer@example.test'): Officer
    {
        return Officer::create([
            'name' => 'Officer '.$email, 'email' => $email,
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(string $email = 'resident@example.test'): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create([
            'name' => 'Resident '.$email, 'email' => $email,
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'pv').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_issue_media_is_private_and_owner_scoped(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $alice = $this->resident('alice@example.test');
        $bob = $this->resident('bob@example.test');
        $officer = $this->officer();

        $res = $this->actingAs($alice, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues', [
                'category' => 'Road / Pavement', 'title' => 'Pothole', 'description' => 'x',
                'images' => [$this->pngUpload('pothole.png')],
            ])
            ->assertOk();
        $this->assertNotEmpty($res->json('issue.media'), 'the photo must be attached');

        $media = Media::where('entity_type', 'IssueReport')->firstOrFail();

        // Stored on the private disk, and the payload points at the authorized route.
        Storage::disk('local')->assertExists($media->file_path);
        $this->assertStringStartsWith('/api/issues/media/', $res->json('issue.media.0.url'));

        // Owner may stream it; another resident may not; staff may.
        $this->actingAs($alice, 'resident')->get('/api/issues/media/'.$media->id)->assertOk();
        $this->actingAs($bob, 'resident')->get('/api/issues/media/'.$media->id)->assertStatus(403);
        $this->actingAs($officer, 'officer')->get('/api/issues/media/'.$media->id)->assertOk();
    }

    public function test_officer_files_live_privately_and_downloads_are_scoped(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $alice = $this->officer('alice.o@example.test');
        $bob = $this->officer('bob.o@example.test');
        $admin = $this->admin();

        $res = $this->actingAs($bob, 'officer')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/officer-files', [
                'file' => UploadedFile::fake()->create('report.pdf', 12, 'application/pdf'),
                'category' => 'Report',
            ])
            ->assertOk();

        $path = $res->json('file.file_path');
        Storage::disk('local')->assertExists($path);

        $id = $res->json('file.id');

        // Sender can download; another officer cannot; an admin can.
        $this->actingAs($bob, 'officer')->get('/api/officer-files/'.$id.'/download')->assertOk();
        $this->actingAs($alice, 'officer')->get('/api/officer-files/'.$id.'/download')->assertStatus(403);
        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->get('/api/officer-files/'.$id.'/download')->assertOk();
    }

    public function test_expense_receipts_are_private_but_stream_to_every_portal(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = $this->admin();
        $resident = $this->resident();

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->post('/api/hoa-expenses', [
                'title' => 'Street lights', 'description' => 'x', 'amount' => 100,
                'expense_date' => '2026-09-15', 'category' => 'Maintenance',
                'receipt' => $this->pngUpload('receipt.png'),
            ])
            ->assertOk();

        $expense = HoaExpense::findOrFail($res->json('expense.id'));
        Storage::disk('local')->assertExists($expense->receipt_path);
        $this->assertSame('/api/hoa-expenses/'.$expense->id.'/receipt', $res->json('expense.receipt_url'));

        // Transparency: residents may stream expense receipts.
        $this->actingAs($resident, 'resident')
            ->get('/api/hoa-expenses/'.$expense->id.'/receipt')->assertOk();
    }
}
