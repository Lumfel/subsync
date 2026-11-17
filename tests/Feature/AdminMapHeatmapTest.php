<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapHeatmapTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Map Admin',
            'email' => 'mapadmin@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Officer Monitor',
            'email' => 'officer-monitor@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        return Resident::firstOrCreate(
            ['email' => 'resident-map@example.test'],
            ['name' => 'Map Resident', 'password' => 'Secret@12345', 'status' => 'Active']
        );
    }

    public function test_api_map_issues_includes_priority_and_category_for_heat_weighting(): void
    {
        $admin = $this->admin();
        $resident = $this->resident();

        IssueReport::create([
            'resident_id' => $resident->id,
            'title' => 'Critical Drainage Failure',
            'category' => 'Drainage / Flooding',
            'description' => 'Flooding at main intersection',
            'status' => 'Pending',
            'priority' => 'Critical',
            'latitude' => 10.62250,
            'longitude' => 122.96120,
        ]);

        $res = $this->actingAs($admin, 'admin')
            ->withSession(['admin_id' => $admin->id, 'active_role' => 'admin'])
            ->getJson('/api/map/issues')
            ->assertOk();

        $data = $res->json();
        $this->assertNotEmpty($data);
        $issue = $data[0];
        $this->assertSame('Drainage / Flooding', $issue['category']);
        $this->assertSame('Critical', $issue['priority']);
        $this->assertSame('Pending', $issue['status']);
        $this->assertEquals(10.62250, (float) $issue['latitude']);
        $this->assertEquals(122.96120, (float) $issue['longitude']);
    }

    public function test_admin_dashboard_renders_thermal_heatmap_and_hotspot_categorization_controls(): void
    {
        $html = view('layouts.admin-dashboard', [
            'adminName' => 'Admin',
            'adminId' => 1,
            'prefs' => [],
            'role' => 'admin',
        ])->render();

        // Heatmap toggle and category filter
        $this->assertStringContainsString('id="layer-heat"', $html);
        $this->assertStringContainsString('id="heat-category"', $html);

        // Heatmap analysis card and live category ranking container
        $this->assertStringContainsString('id="heatmap-analysis-card"', $html);
        $this->assertStringContainsString('id="heatmap-category-pills"', $html);
        $this->assertStringContainsString('id="heatmap-summary-line"', $html);

        // Core thermal heatmap and category categorization logic
        $this->assertStringContainsString('function renderIssueHeatmap', $html);
        $this->assertStringContainsString('L.heatLayer', $html);
        $this->assertStringContainsString('filterHeatmapToCategory', $html);
        $this->assertStringContainsString('sub-hotspot-pill', $html);
        $this->assertStringContainsString('★ Most Frequent Issue Category', $html);
        $this->assertStringContainsString('ensureLeafletHeat', $html);
        $this->assertStringContainsString('leaflet-heat.js', $html);
    }

    public function test_officer_portal_renders_heatmap_toggle_and_analysis_controls(): void
    {
        $officer = $this->officer();
        $html = view('layouts.officer-portal', [
            'officerName' => $officer->name,
            'officerId' => $officer->id,
            'officerEmail' => $officer->email,
            'avatar' => null,
            'prefs' => [],
            'role' => 'officer',
        ])->render();

        $this->assertStringContainsString('id="officerLayerHeat"', $html);
        $this->assertStringContainsString('id="officerHeatCategory"', $html);
        $this->assertStringContainsString('id="officerHeatAnalysisCard"', $html);
        $this->assertStringContainsString('id="officerHeatCategoryPills"', $html);

        $js = file_get_contents(public_path('js/officer-portal.js'));
        $this->assertStringContainsString('function renderOfficerHeatmap', $js);
        $this->assertStringContainsString('function toggleOfficerHeatmap', $js);
        $this->assertStringContainsString('filterOfficerHeatmapToCategory', $js);
        $this->assertStringContainsString('leaflet-heat.js', $js);
    }

    public function test_resident_portal_renders_view_only_layer_controls_and_heatmap(): void
    {
        $html = file_get_contents(resource_path('views/layouts/residents.blade.php'));

        // Layer toggles
        $this->assertStringContainsString('id="res-layer-blocks"', $html);
        $this->assertStringContainsString('id="res-layer-facilities"', $html);
        $this->assertStringContainsString('id="res-layer-issues"', $html);
        $this->assertStringContainsString('id="res-layer-heat"', $html);
        $this->assertStringContainsString('id="res-heat-category"', $html);

        // Heatmap analysis card
        $this->assertStringContainsString('id="res-heatmap-analysis-card"', $html);
        $this->assertStringContainsString('id="res-heatmap-category-pills"', $html);
        $this->assertStringContainsString('function renderResidentHeatmap', $html);
        $this->assertStringContainsString('function toggleResidentFacilities', $html);
        $this->assertStringContainsString('function toggleResidentIssues', $html);
        $this->assertStringContainsString('function toggleResidentHeatmap', $html);

        // View-only guarantee: no editing powers on resident map
        $this->assertStringNotContainsString('enterEditBlocksMode', $html);
        $this->assertStringNotContainsString('enterCustomizeArea', $html);
        $this->assertStringNotContainsString('enterAddFacilityMode', $html);
    }

    public function test_admin_dashboard_renders_view_mode_and_edit_mode_switch(): void
    {
        $html = view('layouts.admin-dashboard', [
            'adminName' => 'Admin',
            'adminId' => 1,
            'prefs' => [],
            'role' => 'admin',
        ])->render();

        $this->assertStringContainsString('id="btn-map-mode-view"', $html);
        $this->assertStringContainsString('id="btn-map-mode-edit"', $html);
        $this->assertStringContainsString('function setMapInteractiveMode', $html);
    }

    public function test_dashboard_mini_map_renders_colored_blocks_and_heatmap_without_text_labels_or_pins(): void
    {
        $html = view('layouts.admin-dashboard', [
            'adminName' => 'Admin',
            'adminId' => 1,
            'prefs' => [],
            'role' => 'admin',
        ])->render();

        $this->assertStringContainsString('id="dash-mini-map"', $html);
        $this->assertStringContainsString('dashMiniHeatLayer', $html);
        $this->assertStringContainsString('dashMiniBlocksLayer', $html);
        $this->assertStringContainsString('function initDashboardMiniMap', $html);
        $this->assertStringContainsString('function refreshDashboardMiniMapData', $html);
    }

    public function test_ui_polish_css_contains_hotspot_styles_and_pulse_animations(): void
    {
        $css = file_get_contents(public_path('css/ui-polish.css'));
        $this->assertStringContainsString('.sub-hotspot-pill', $css);
        $this->assertStringContainsString('.sub-hotspot-dominant-box', $css);
        $this->assertStringContainsString('.sub-hotspot-bar-fill', $css);
        $this->assertStringContainsString('@keyframes hotspot-pulse-critical', $css);
        $this->assertStringContainsString('.heatmap-pill-btn', $css);
    }
}
