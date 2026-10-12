<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\OfficerFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase1FixesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'img').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_announcement_includes_date_and_time(): void
    {
        $res = $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'Test Admin'])
            ->postJson('/api/announcements', ['title' => 'Community Meeting', 'content' => 'Please attend.']);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertMatchesRegularExpression(
            '/\d{1,2}:\d{2}\s?(AM|PM)/',
            $res->json('announcement.created_at')
        );
    }

    public function test_admin_can_archive_officer_file(): void
    {
        $file = OfficerFile::create([
            'officer_name' => 'Admin',
            'original_name' => 'report.pdf',
            'category' => 'Report',
            'size_bytes' => 1234,
            'archived' => false,
        ]);

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->patchJson('/api/officer-files/'.$file->id.'/archive');

        $res->assertOk()->assertJson(['success' => true, 'archived' => true]);
        $this->assertDatabaseHas('officer_files', ['id' => $file->id, 'archived' => true]);
    }

    public function test_officer_file_upload_accepts_images(): void
    {
        Storage::fake('public');

        $res = $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'Test Admin'])
            ->post('/api/officer-files', [
                'file' => $this->pngUpload('receipt.png'),
                'category' => 'Report',
            ]);

        $res->assertOk()->assertJson(['success' => true]);
    }

    public function test_officer_file_upload_rejects_disallowed_type(): void
    {
        Storage::fake('public');

        $res = $this->withHeaders(['Accept' => 'application/json'])
            ->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'Test Admin'])
            ->post('/api/officer-files', [
                'file' => UploadedFile::fake()->create('script.exe', 5, 'application/octet-stream'),
                'category' => 'Report',
            ]);

        $res->assertStatus(422);
    }
}
