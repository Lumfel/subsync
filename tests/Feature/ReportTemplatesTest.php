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

    public function test_resident_can_list_report_templates(): void
    {
        $this->template();

        $this->actingAs($this->resident(), 'resident')
            ->getJson('/api/report-templates')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Barangay Clearance']);
    }

    public function test_resident_can_generate_a_report_from_a_template(): void
    {
        $template = $this->template();
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/reports/generate/'.$template->id, [
                'variables' => ['resident_name' => 'Juan', 'block_lot' => 'Block 1 Lot 1'],
            ])
            ->assertOk()
            ->assertJsonPath('report.subject', 'Clearance for Juan');

        $this->assertDatabaseHas('reports', [
            'resident_id' => $resident->id,
            'template_id' => $template->id,
            'subject' => 'Clearance for Juan',
        ]);
    }

    public function test_resident_can_view_their_generated_reports(): void
    {
        $template = $this->template();
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/reports/generate/'.$template->id, ['variables' => ['resident_name' => 'Juan', 'block_lot' => 'B1']])
            ->assertOk();

        $this->actingAs($resident, 'resident')
            ->getJson('/api/reports/my')
            ->assertOk()
            ->assertJsonFragment(['subject' => 'Clearance for Juan']);
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

    public function test_report_print_view_is_owner_or_staff_only(): void
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

        // Owner resident can print.
        $this->as('resident', $resident);
        $this->get('/reports/'.$id.'/print')->assertOk()->assertSee($ref);

        // Another resident cannot.
        $other = Resident::create(['name' => 'Other', 'email' => 'o@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
        $this->as('resident', $other);
        $this->get('/reports/'.$id.'/print')->assertStatus(403);

        // Staff can print.
        $this->as('admin', $admin);
        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin A']);
        $this->get('/reports/'.$id.'/print')->assertOk();
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
