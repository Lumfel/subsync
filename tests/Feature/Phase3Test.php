<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\IssueReport;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase3Test extends TestCase
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

    private function resident(): Resident
    {
        return Resident::create([
            'name' => 'Test Resident',
            'email' => 'resident@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function pdfUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );
    }

    public function test_resident_can_attach_supporting_documents_to_issue(): void
    {
        Storage::fake('public');
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues', [
                'category' => 'Other',
                'title' => 'Damaged fence',
                'description' => 'Fence near the park is damaged.',
                'documents' => [$this->pdfUpload('evidence.pdf')],
            ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('media', [
            'entity_type' => 'IssueReport',
            'file_name' => 'evidence.pdf',
        ]);
    }

    public function test_announcement_templates_are_listed(): void
    {
        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->getJson('/api/announcement-templates');

        $res->assertOk();
        $this->assertGreaterThanOrEqual(3, count($res->json()));
        $res->assertJsonFragment(['name' => 'Community Notice']);
    }

    public function test_admin_can_archive_issue_and_it_is_hidden_from_active_list(): void
    {
        $admin = $this->admin();
        $issue = IssueReport::create([
            'resident_id' => $this->resident()->id,
            'category' => 'Other',
            'title' => 'Old issue',
            'description' => 'Already handled.',
            'status' => 'Resolved',
        ]);

        $this->withSession(['admin_id' => $admin->id])
            ->patchJson('/api/issues/'.$issue->id.'/archive')
            ->assertOk()
            ->assertJson(['success' => true, 'archived' => true]);

        $this->assertNotNull($issue->fresh()->archived_at);

        $active = collect($this->withSession(['admin_id' => $admin->id])
            ->getJson('/api/issues')->json())->pluck('id');
        $this->assertFalse($active->contains($issue->id));

        $archived = collect($this->withSession(['admin_id' => $admin->id])
            ->getJson('/api/issues?archived=1')->json())->pluck('id');
        $this->assertTrue($archived->contains($issue->id));
    }
}
