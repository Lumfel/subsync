<?php

namespace Tests\Feature;

use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResidentAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create([
            'name' => 'Test Resident', 'email' => 'resident@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'av').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_resident_can_update_profile_avatar(): void
    {
        Storage::fake('public');
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/residents/avatar', ['avatar' => $this->pngUpload('me.png')]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertNotNull($resident->fresh()->avatar);
    }

    public function test_guest_cannot_update_avatar(): void
    {
        Storage::fake('public');

        $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/residents/avatar', ['avatar' => $this->pngUpload('me.png')])
            ->assertStatus(401);
    }
}
