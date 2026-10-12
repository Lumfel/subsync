<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GcashSettingTest extends TestCase
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

    public function test_all_roles_can_read_gcash_settings(): void
    {
        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->getJson('/api/gcash-settings');

        $res->assertOk()->assertJsonStructure([
            'account_name', 'account_number', 'instructions', 'qr_image_url',
        ]);
    }

    public function test_admin_can_update_account_details(): void
    {
        $admin = $this->admin();

        $res = $this->withSession(['admin_id' => $admin->id])
            ->postJson('/api/gcash-settings', [
                'account_name' => 'TNHOA Treasurer',
                'account_number' => '09171234567',
            ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('gcash_settings', [
            'id' => 1,
            'account_name' => 'TNHOA Treasurer',
            'account_number' => '09171234567',
        ]);
    }

    public function test_admin_can_upload_and_remove_qr_code(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'qr').'.png';
        file_put_contents($tmp, $png);
        $image = new UploadedFile($tmp, 'gcash-qr.png', 'image/png', null, true);

        $upload = $this->withSession(['admin_id' => $admin->id])
            ->post('/api/gcash-settings', ['qr_image' => $image]);

        $upload->assertOk();
        $path = $upload->json('settings.qr_image_path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $remove = $this->withSession(['admin_id' => $admin->id])
            ->post('/api/gcash-settings', ['remove_qr' => '1']);

        $remove->assertOk();
        $this->assertNull($remove->json('settings.qr_image_path'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_non_admin_cannot_update_gcash_settings(): void
    {
        $this->postJson('/api/gcash-settings', ['account_name' => 'Hacked'])
            ->assertStatus(401);

        $this->assertDatabaseMissing('gcash_settings', ['account_name' => 'Hacked']);
    }
}
