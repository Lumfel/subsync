<?php

namespace Tests\Feature;

use App\Http\Controllers\MapController;
use App\Models\Admin;
use App\Models\Facility;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Executes the SubSync acceptance test cases TC-01 … TC-10.
 */
class AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Officer $officer;

    private Resident $resident;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['name' => 'Admin', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
        $this->officer = Officer::create(['name' => 'Officer', 'email' => 'o@x.com', 'password' => 'Secret@12345', 'status' => 'Active', 'role_description' => 'Secretary']);
        $this->household = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $this->resident = Resident::create([
            'name' => 'Resident', 'email' => 'r@x.com', 'password' => 'Secret@12345',
            'status' => 'Active', 'house_id' => $this->household->id,
        ]);
    }

    private function as(string $role, $user): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        if ($role === 'admin') {
            $this->withSession(['admin_id' => $user->id, 'admin_name' => $user->name]);
        } else {
            $this->actingAs($user, $role);
        }
    }

    private function png(string $name = 'photo.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'img').'.png';
        file_put_contents($tmp, $bytes);

        return new UploadedFile($tmp, $name, 'image/png', null, true);
    }

    // ── TC-01 User Authentication ──
    public function test_tc01_user_authentication_valid_credentials(): void
    {
        $this->as('admin', $this->admin);
        $this->postJson('/admin-access', ['email' => 'a@x.com', 'password' => 'Secret@12345'])
            ->assertOk()->assertJson(['success' => true])
            ->assertJsonPath('redirect', fn ($r) => str_ends_with($r, '/dashboard'));

        $this->as('resident', $this->resident);
        $this->postJson('/login', ['email' => 'r@x.com', 'password' => 'Secret@12345', 'role' => 'resident'])
            ->assertOk()->assertJson(['success' => true]);

        $this->as('officer', $this->officer);
        $this->postJson('/login', ['email' => 'o@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertAuthenticatedAs($this->officer, 'officer');
    }

    // ── TC-02 Announcement Module ──
    public function test_tc02_admin_can_post_announcement_visible_to_resident(): void
    {
        $this->as('admin', $this->admin);
        $this->postJson('/api/announcements', ['title' => 'Water Interruption', 'content' => 'No water 8-10am'])
            ->assertOk()->assertJson(['success' => true]);

        $this->as('resident', $this->resident);
        $titles = array_column($this->getJson('/api/announcements')->assertOk()->json(), 'title');
        $this->assertContains('Water Interruption', $titles);
    }

    // ── TC-03 Issue Reporting ──
    public function test_tc03_resident_can_submit_issue_visible_to_staff(): void
    {
        $this->as('resident', $this->resident);
        $res = $this->postJson('/api/issues', ['category' => 'Noise', 'title' => 'Loud karaoke', 'description' => 'Every night'])
            ->assertOk()->assertJson(['success' => true]);
        $issueId = $res->json('issue.id');

        $this->as('admin', $this->admin);
        $ids = array_column($this->getJson('/api/issues')->assertOk()->json(), 'id');
        $this->assertContains($issueId, $ids);
    }

    // ── TC-04 Issue Monitoring ──
    public function test_tc04_officer_can_respond_and_update_issue_status(): void
    {
        $issue = IssueReport::create(['resident_id' => $this->resident->id, 'category' => 'Noise', 'title' => 'X', 'description' => 'Y', 'status' => 'Pending']);

        $this->as('officer', $this->officer);
        $this->postJson("/api/issues/{$issue->id}/respond", ['content' => 'We are on it.'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame('In Progress', $issue->fresh()->status);

        $this->as('officer', $this->officer);
        $this->putJson("/api/issues/{$issue->id}/status", ['status' => 'Resolved'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Resolved', $issue->fresh()->status);
    }

    // ── TC-05 Recommendation Module ──
    public function test_tc05_resident_can_submit_recommendation(): void
    {
        $this->as('resident', $this->resident);
        $this->postJson('/api/recommendations', ['title' => 'Add speed bump', 'description' => 'Near the gate'])
            ->assertOk()->assertJson(['success' => true])
            ->assertJsonPath('recommendation.status', 'Pending');

        $titles = array_column($this->getJson('/api/recommendations/my')->assertOk()->json(), 'title');
        $this->assertContains('Add speed bump', $titles);
    }

    // ── TC-06 Mapping Feature (display) ──
    public function test_tc06_map_displays_facilities_and_issue_markers(): void
    {
        $b = MapController::bounds();
        $lat = $b['center_lat'];
        $lng = $b['center_lng'];

        Facility::create(['name' => 'Clubhouse', 'latitude' => $lat, 'longitude' => $lng, 'status' => 'Operational']);
        IssueReport::create(['resident_id' => $this->resident->id, 'category' => 'Noise', 'title' => 'Marker', 'description' => 'x', 'status' => 'Pending', 'latitude' => $lat, 'longitude' => $lng]);

        $this->as('resident', $this->resident);
        $data = $this->getJson('/api/map/all')->assertOk()
            ->assertJsonStructure(['households', 'facilities', 'issues'])->json();

        $this->assertContains('Clubhouse', array_column($data['facilities'], 'name'));
        $this->assertNotEmpty($data['issues']);

        $this->getJson('/api/map/settings')->assertOk()->assertJsonStructure(['min_lat', 'max_lat', 'min_lng', 'max_lng', 'center_lat', 'zoom']);
    }

    // ── TC-07 Mapping Feature (invalid location) ──
    public function test_tc07_invalid_location_is_rejected(): void
    {
        $this->as('resident', $this->resident);
        // (0,0) is far outside the configured Terra Nova bounds.
        $this->postJson('/api/issues', ['category' => 'Noise', 'title' => 'Out', 'description' => 'x', 'latitude' => 0, 'longitude' => 0])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->as('admin', $this->admin);
        $this->putJson("/api/map/household/{$this->household->id}/location", ['latitude' => 0, 'longitude' => 0])
            ->assertStatus(422);
    }

    // ── TC-08 Geographic Issue Monitoring (direct messaging) ──
    public function test_tc08_direct_message_resident_to_officer(): void
    {
        // Resident starts a conversation with the officer (they become the initiator).
        $this->as('resident', $this->resident);
        $convId = $this->postJson('/api/messages/start', [
            'title' => 'Gate pass request', 'recipient_type' => 'officer', 'recipient_id' => $this->officer->id,
        ])->assertOk()->json('conversation.id');

        $this->as('resident', $this->resident);
        $this->postJson("/api/messages/{$convId}", ['content' => 'Hello officer'])
            ->assertOk()->assertJson(['success' => true]);

        // Officer sees it as a message request and can read it.
        $this->as('officer', $this->officer);
        $requests = $this->getJson('/api/messages/requests')->assertOk()->json();
        $this->assertContains($convId, array_column($requests, 'id'));

        $contents = array_column($this->getJson("/api/messages/{$convId}")->assertOk()->json(), 'content');
        $this->assertContains('Hello officer', $contents);

        // After accepting, the officer can reply.
        $this->as('officer', $this->officer);
        $this->postJson("/api/messages/{$convId}/accept")->assertOk();
        $this->postJson("/api/messages/{$convId}", ['content' => 'On my way'])
            ->assertOk()->assertJson(['success' => true]);
    }

    // ── TC-09 Media Upload ──
    public function test_tc09_image_and_file_uploads(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        // Resident issue photo
        $this->as('resident', $this->resident);
        $this->postJson('/api/issues', ['category' => 'Noise', 'title' => 'With photo', 'description' => 'x', 'images' => [$this->png()]])
            ->assertOk()->assertJson(['success' => true]);

        // Resident avatar
        $this->as('resident', $this->resident);
        $this->postJson('/api/residents/avatar', ['avatar' => $this->png('me.png')])
            ->assertOk()->assertJson(['success' => true]);

        // Officer document
        $this->as('officer', $this->officer);
        $res = $this->postJson('/api/officer-files', ['file' => UploadedFile::fake()->create('report.pdf', 12, 'application/pdf'), 'category' => 'Reports'])
            ->assertOk()->assertJson(['success' => true]);
        Storage::disk('local')->assertExists($res->json('file.file_path'));
    }

    // ── TC-10 Compatibility & Performance (automated partial) ──
    public function test_tc10_pages_are_mobile_ready_and_load(): void
    {
        $this->get('/login')->assertOk()->assertSee('viewport', false);

        $this->as('admin', $this->admin);
        $this->get('/dashboard')->assertOk();

        $this->as('officer', $this->officer);
        $this->get('/officer-portal')->assertOk();

        $this->as('resident', $this->resident);
        $this->get('/residents')->assertOk();
    }
}
