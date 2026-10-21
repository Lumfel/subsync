<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ReportTemplate;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(): Resident
    {
        return Resident::create(['name' => 'Juan Dela Cruz', 'email' => 'j@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function template(): ReportTemplate
    {
        return ReportTemplate::create([
            'name' => 'Barangay Clearance',
            'type' => 'Clearance',
            'subject_template' => 'Clearance for {{{resident_name}}}',
            'body_template' => 'Dear {{{resident_name}}}, your clearance for {{{block_lot}}} is ready.',
            'variables' => json_encode(['resident_name', 'block_lot']),
        ]);
    }

    public function test_residents_cannot_access_reports(): void
    {
        $this->template();
        $resident = $this->resident();

        // Templates are a staff tool.
        $this->actingAs($resident, 'resident')
            ->getJson('/api/report-templates')
            ->assertStatus(401);

        // Staff-only issued documents list.
        $this->actingAs($resident, 'resident')
            ->getJson('/api/reports')
            ->assertStatus(401);

        // Resident self-service report routes no longer exist.
        $this->actingAs($resident, 'resident')
            ->getJson('/api/reports/my')
            ->assertStatus(404);

        $this->actingAs($resident, 'resident')
            ->postJson('/api/reports/generate/1', ['variables' => []])
            ->assertStatus(404);
    }

    public function test_staff_can_list_report_templates(): void
    {
        $this->template();

        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->getJson('/api/report-templates')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Barangay Clearance']);
    }

    public function test_resident_cannot_manage_templates(): void
    {
        $this->actingAs($this->resident(), 'resident')
            ->postJson('/api/report-templates', ['name' => 'X', 'type' => 'X', 'subject_template' => 'X', 'body_template' => 'X'])
            ->assertStatus(401);
    }

    public function test_staff_can_create_a_template(): void
    {
        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->postJson('/api/report-templates', [
                'name' => 'Statement', 'type' => 'Finance',
                'subject_template' => 'Statement for {{{resident_name}}}',
                'body_template' => 'Balance: {{{balance}}}',
            ])
            ->assertOk();

        $this->assertDatabaseHas('report_templates', ['name' => 'Statement']);
    }

    private function as(string $role, $user): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->actingAs($user, $role);
    }

    public function test_staff_can_issue_a_document_for_a_resident(): void
    {
        $template = $this->template();
        $resident = $this->resident();
        $admin = $this->admin();

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A'])
            ->postJson('/api/reports/issue', [
                'template_id' => $template->id,
                'resident_id' => $resident->id,
                'variables' => ['resident_name' => 'Juan', 'block_lot' => 'Block 1 Lot 1'],
            ])
            ->assertOk()
            ->assertJsonPath('report.status', 'Issued')
            ->assertJsonPath('report.issued_by', 'Admin A');

        $this->assertStringStartsWith('CLEA-', (string) $res->json('report.reference_no'));
        $this->assertDatabaseHas('reports', ['resident_id' => $resident->id, 'issued_by' => 'Admin A']);
    }

    public function test_staff_list_shows_issued_documents(): void
    {
        $template = $this->template();
        $resident = $this->resident();
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A'])
            ->postJson('/api/reports/issue', [
                'template_id' => $template->id, 'resident_id' => $resident->id,
                'variables' => ['resident_name' => 'Juan', 'block_lot' => 'B1'],
            ])->assertOk();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A'])
            ->getJson('/api/reports')
            ->assertOk()
            ->assertJsonFragment(['resident' => 'Juan Dela Cruz']);
    }

    public function test_report_print_view_is_staff_only(): void
    {
        $template = $this->template();
        $resident = $this->resident();
        $admin = $this->admin();

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A'])
            ->postJson('/api/reports/issue', [
                'template_id' => $template->id, 'resident_id' => $resident->id,
                'variables' => ['resident_name' => 'Juan', 'block_lot' => 'B1'],
            ])->assertOk();
        $id = $res->json('report.id');
        $ref = $res->json('report.reference_no');

        // Residents cannot print documents, even their own.
        $this->as('resident', $resident);
        $this->get('/reports/'.$id.'/print')->assertStatus(403);

        // Staff can print.
        $this->as('admin', $admin);
        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A']);
        $this->get('/reports/'.$id.'/print')->assertOk()->assertSee($ref);
    }

    public function test_issuing_requires_all_template_variables(): void
    {
        $template = $this->template();
        $resident = $this->resident();

        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->postJson('/api/reports/issue', [
                'template_id' => $template->id, 'resident_id' => $resident->id,
                'variables' => ['resident_name' => 'Juan'], // block_lot missing
            ])
            ->assertStatus(400);
    }
}
