<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResidentIssueDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function resident(string $name, string $email): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create([
            'name' => $name, 'email' => $email,
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);
    }

    private function issue(Resident $resident, string $status = 'Pending'): IssueReport
    {
        return IssueReport::create([
            'resident_id' => $resident->id,
            'category' => 'Road / Pavement',
            'title' => 'Pothole',
            'description' => 'There is a pothole.',
            'status' => $status,
            'priority' => 'Low',
        ]);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'dt').'.png';
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    public function test_owner_can_append_text_details(): void
    {
        $alice = $this->resident('Alice', 'alice@example.test');
        $issue = $this->issue($alice);

        $this->actingAs($alice, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', ['details' => 'It got bigger overnight.'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('issue_audit_logs', [
            'issue_id' => $issue->id, 'action' => 'details', 'actor_type' => 'Resident',
        ]);

        // The resident's own timeline includes the addition.
        $res = $this->actingAs($alice, 'resident')
            ->getJson('/api/issues/my/'.$issue->id.'/timeline')
            ->assertOk();
        $this->assertSame('details', $res->json('timeline.0.action'));
    }

    public function test_owner_can_append_a_photo(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $alice = $this->resident('Alice', 'alice@example.test');
        $issue = $this->issue($alice);

        $this->actingAs($alice, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', ['images' => [$this->pngUpload('more.png')]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('media', ['entity_type' => 'IssueReport', 'entity_id' => $issue->id]);
        $this->assertDatabaseHas('issue_audit_logs', ['issue_id' => $issue->id, 'action' => 'attachments']);
    }

    public function test_another_resident_cannot_append_details(): void
    {
        $alice = $this->resident('Alice', 'alice@example.test');
        $bob = $this->resident('Bob', 'bob@example.test');
        $issue = $this->issue($alice);

        $this->actingAs($bob, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', ['details' => 'hijack'])
            ->assertNotFound();
    }

    public function test_resolved_reports_reject_new_details(): void
    {
        $alice = $this->resident('Alice', 'alice@example.test');
        $issue = $this->issue($alice, 'Resolved');

        $this->actingAs($alice, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', ['details' => 'one more thing'])
            ->assertStatus(422);
    }

    public function test_empty_update_is_rejected(): void
    {
        $alice = $this->resident('Alice', 'alice@example.test');
        $issue = $this->issue($alice);

        $this->actingAs($alice, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', [])
            ->assertStatus(422);
    }

    public function test_staff_cannot_use_the_resident_endpoint(): void
    {
        $alice = $this->resident('Alice', 'alice@example.test');
        $issue = $this->issue($alice);

        $officer = Officer::create([
            'name' => 'Officer', 'email' => 'o@x.com',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);

        $this->actingAs($officer, 'officer')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/issues/'.$issue->id.'/details', ['details' => 'note'])
            ->assertStatus(401);
    }
}
