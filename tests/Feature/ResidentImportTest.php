<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ResidentImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function csv(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('residents.csv', $content);
    }

    public function test_staff_can_import_residents_from_csv(): void
    {
        $file = $this->csv(
            "name,email,contact_number,block_lot\n".
            "Juan Dela Cruz,juan@x.com,0917,Block 1 Lot 1\n".
            "Ana Garcia,ana@x.com,,Block 1 Lot 1\n".
            "Juan Again,juan@x.com,,Block 2 Lot 2\n" // duplicate email -> skipped
        );

        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->post('/api/import/residents', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('skipped', 1);

        $this->assertDatabaseHas('residents', ['email' => 'juan@x.com', 'contact_number' => '0917']);
        $this->assertDatabaseHas('residents', ['email' => 'ana@x.com']);
        $this->assertDatabaseHas('households', ['block_lot_number' => 'Block 1 Lot 1']);
    }

    public function test_import_requires_name_and_email_columns(): void
    {
        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->post('/api/import/residents', ['file' => $this->csv("foo,bar\n1,2\n")])
            ->assertStatus(422);
    }

    public function test_resident_cannot_import(): void
    {
        $resident = Resident::create(['name' => 'R', 'email' => 'r@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);

        $this->actingAs($resident, 'resident')
            ->post('/api/import/residents', ['file' => $this->csv("name,email\nX,x@x.com\n")])
            ->assertStatus(401);
    }
}
