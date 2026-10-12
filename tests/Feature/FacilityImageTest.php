<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function png(string $name = 'facility.png'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'fac').'.png';
        file_put_contents($tmp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_admin_can_create_a_facility_with_an_image(): void
    {
        Storage::fake('public');

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/map/facility', [
                'name' => 'Clubhouse',
                'latitude' => 10.62269,
                'longitude' => 122.96134,
                'image' => $this->png(),
            ]);

        $res->assertOk()->assertJsonPath('success', true);
        $path = $res->json('facility.image_path');
        $this->assertNotNull($path);
        $this->assertStringContainsString('/storage/', $res->json('facility.image_url'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_facilities_api_exposes_image_url(): void
    {
        Facility::create([
            'name' => 'Pool', 'description' => '', 'status' => 'Active',
            'latitude' => 10.62269, 'longitude' => 122.96134, 'image_path' => 'facilities/pool.png',
        ]);

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/map/facilities');

        $res->assertOk();
        $this->assertStringContainsString('pool.png', $res->json('0.image_url'));
    }

    public function test_updating_replaces_the_image(): void
    {
        Storage::fake('public');
        $facility = Facility::create([
            'name' => 'Court', 'status' => 'Active', 'latitude' => 10.62269, 'longitude' => 122.96134,
            'image_path' => 'facilities/old.png',
        ]);
        Storage::disk('public')->put('facilities/old.png', 'old');

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->post("/api/map/facility/{$facility->id}", [
                '_method' => 'PUT',
                'name' => 'Court',
                'image' => $this->png('new.png'),
            ])
            ->assertOk();

        $fresh = $facility->fresh();
        $this->assertNotSame('facilities/old.png', $fresh->image_path);
        Storage::disk('public')->assertMissing('facilities/old.png');
    }

    public function test_removing_the_image(): void
    {
        Storage::fake('public');
        $facility = Facility::create([
            'name' => 'Gym', 'status' => 'Active', 'latitude' => 10.62269, 'longitude' => 122.96134,
            'image_path' => 'facilities/gym.png',
        ]);

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson("/api/map/facility/{$facility->id}", ['remove_image' => true])
            ->assertOk();

        $this->assertNull($facility->fresh()->image_path);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('notes.txt', 5, 'text/plain');

        $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/map/facility', [
                'name' => 'Bad', 'latitude' => 10.62269, 'longitude' => 122.96134, 'image' => $file,
            ])
            ->assertStatus(422);
    }

    public function test_real_multipart_upload_persists_file(): void
    {
        // Uses the real 'public' disk (no Storage::fake) to exercise the true path.
        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/map/facility', [
                'name' => 'Real Upload', 'latitude' => 10.62269, 'longitude' => 122.96134, 'image' => $this->png(),
            ]);

        $res->assertOk()->assertJsonPath('success', true);
        $path = $res->json('facility.image_path');
        $this->assertTrue(Storage::disk('public')->exists($path));
        Storage::disk('public')->delete($path); // cleanup
    }
}
