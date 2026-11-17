<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MapSetting;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMapBlueprintOverlayTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_block_partition_controls_and_omits_blueprint_overlay(): void
    {
        $view = view('layouts.admin-dashboard', [
            'adminName' => 'Super Admin',
            'adminId' => 1,
            'prefs' => [],
            'role' => 'admin',
        ])->render();

        // Block partition controls rendered
        $this->assertStringContainsString('id="btn-edit-blocks"', $view);
        $this->assertStringContainsString('id="btn-subdivide-block"', $view);
        $this->assertStringContainsString('id="btn-block-mode-scale"', $view);
        $this->assertStringContainsString('id="btn-block-mode-points"', $view);
        $this->assertStringContainsString('id="btn-shrink-block"', $view);
        $this->assertStringContainsString('id="btn-enlarge-block"', $view);
        $this->assertStringContainsString('id="btn-edit-block-info"', $view);
        $this->assertStringContainsString('id="modal-editBlock"', $view);
        $this->assertStringContainsString('id="map-blocks-toolbar"', $view);
        $this->assertStringContainsString('id="layer-blocks"', $view);
        $this->assertStringContainsString('id="filter-block-select"', $view);
        $this->assertStringContainsString('function enterEditBlocksMode', $view);
        $this->assertStringContainsString('function setBlockEditorMode', $view);
        $this->assertStringContainsString('function createBlockTransformHandles', $view);
        $this->assertStringContainsString('function handleScaleDrag', $view);
        $this->assertStringContainsString('function scaleSelectedBlock', $view);
        $this->assertStringContainsString('function openEditBlockModal', $view);
        $this->assertStringContainsString('function saveBlockDetails', $view);
        $this->assertStringContainsString('function subdivideBlockPoints', $view);
        $this->assertStringContainsString('function saveBlockPolygons', $view);
        $this->assertStringContainsString('function loadBlocksData', $view);

        // Blueprint overlay controls removed
        $this->assertStringNotContainsString('id="btn-align-overlay"', $view);
        $this->assertStringNotContainsString('id="map-overlay-toolbar"', $view);
        $this->assertStringNotContainsString('id="layer-overlay"', $view);
        $this->assertStringNotContainsString('id="overlay-opacity-range"', $view);
        $this->assertStringNotContainsString('id="overlay-rotate-input"', $view);
        $this->assertStringNotContainsString('id="overlay-file-input"', $view);
        $this->assertStringNotContainsString('function applyBlueprintOverlay', $view);
        $this->assertStringNotContainsString('function enterAlignOverlay', $view);
    }

    public function test_admin_css_contains_block_partition_classes(): void
    {
        $css = file_get_contents(public_path('css/admin-dashboard.css'));

        $this->assertStringContainsString('.block-vertex-handle', $css);
        $this->assertStringContainsString('.block-midpoint-handle', $css);
        $this->assertStringContainsString('.block-scale-handle', $css);
        $this->assertStringContainsString('.block-scale-handle-corner', $css);
        $this->assertStringContainsString('.block-scale-center', $css);
        $this->assertStringContainsString('.sub-block-pill', $css);
        $this->assertStringContainsString('.sub-block-start-pill', $css);
        $this->assertStringContainsString('--map-pill-scale', $css);
        $this->assertStringContainsString('.map-zoomed-out', $css);
        $this->assertStringContainsString('.map-zoomed-far', $css);
    }

    public function test_admin_can_update_overlay_alignment_settings(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_overlay@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $payload = [
            'overlay_min_lat' => 10.6196123,
            'overlay_max_lat' => 10.6258123,
            'overlay_min_lng' => 122.9582123,
            'overlay_max_lng' => 122.9648123,
            'overlay_opacity' => 0.75,
            'overlay_rotation' => 2.50,
            'overlay_enabled' => true,
        ];

        $response = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->withHeader('X-Portal', 'admin')
            ->putJson('/api/map/settings', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $current = MapSetting::current();
        $this->assertEqualsWithDelta(10.6196123, $current->overlay_min_lat, 0.000001);
        $this->assertEqualsWithDelta(10.6258123, $current->overlay_max_lat, 0.000001);
        $this->assertEqualsWithDelta(0.75, $current->overlay_opacity, 0.01);
        $this->assertEqualsWithDelta(2.50, $current->overlay_rotation, 0.01);
        $this->assertTrue($current->overlay_enabled);
    }

    public function test_admin_can_upload_blueprint_image(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_upload@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $file = UploadedFile::fake()->create('custom_plan.jpg', 100, 'image/jpeg');

        $response = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->withHeader('X-Portal', 'admin')
            ->postJson('/api/map/overlay/upload', [
                'overlay' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $storedPath = str_replace('/storage/', '', $response->json('overlay_url'));
        Storage::disk('public')->assertExists($storedPath);

        $this->assertEquals($response->json('overlay_url'), MapSetting::current()->overlay_url);
    }

    public function test_blocks_api_returns_all_subdivision_blocks_with_lot_ranges(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_blocks@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->withHeader('X-Portal', 'admin')
            ->getJson('/api/map/blocks');

        $response->assertOk()
            ->assertJsonCount(14)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'block_number',
                    'name',
                    'color',
                    'lot_range',
                    'total_lots',
                    'start_label',
                    'start_lat',
                    'start_lng',
                    'label_lat',
                    'label_lng',
                    'coordinates',
                    'households_count',
                    'occupied_count',
                    'vacant_count',
                ],
            ]);

        $block4 = collect($response->json())->firstWhere('block_number', '4');
        $this->assertNotNull($block4);
        $this->assertSame('Lots 1–40', $block4['lot_range']);
        $this->assertSame(40, $block4['total_lots']);
    }

    public function test_admin_can_bulk_update_block_polygon_coordinates(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_update_blocks@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $newCoords = [
            [10.6259, 122.9630],
            [10.6257, 122.9645],
            [10.6252, 122.9643],
            [10.6253, 122.9630],
        ];

        $payload = [
            'blocks' => [
                [
                    'id' => 1,
                    'coordinates' => $newCoords,
                    'start_lat' => 10.6258,
                    'start_lng' => 122.9634,
                    'label_lat' => 10.6255,
                    'label_lng' => 122.9638,
                    'start_label' => 'Lot 1 (North Gate)',
                ],
            ],
        ];

        $response = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->withHeader('X-Portal', 'admin')
            ->putJson('/api/map/blocks', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('subdivision_blocks', [
            'id' => 1,
            'start_lat' => 10.6258,
            'start_lng' => 122.9634,
            'label_lat' => 10.6255,
            'label_lng' => 122.9638,
            'start_label' => 'Lot 1 (North Gate)',
        ]);

        // Asserts SSE event recorded for real-time live update
        $this->assertDatabaseHas('sse_events', [
            'event' => 'block',
        ]);
    }

    public function test_admin_can_update_single_block_details(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_single_block@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $payload = [
            'name' => 'Block 4 (West Wing)',
            'color' => '#10b981',
            'lot_range' => 'Lots 1–45',
            'total_lots' => 45,
            'start_label' => 'Lot 1 (East Corner)',
            'start_lat' => 10.6251234,
            'start_lng' => 122.9638123,
            'label_lat' => 10.6250123,
            'label_lng' => 122.9635123,
            'description' => 'Updated perimeter details',
        ];

        $response = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->withHeader('X-Portal', 'admin')
            ->putJson('/api/map/block/4', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('block.name', 'Block 4 (West Wing)');

        $this->assertDatabaseHas('subdivision_blocks', [
            'id' => 4,
            'name' => 'Block 4 (West Wing)',
            'lot_range' => 'Lots 1–45',
            'total_lots' => 45,
            'start_label' => 'Lot 1 (East Corner)',
            'start_lat' => 10.6251234,
            'start_lng' => 122.9638123,
            'label_lat' => 10.6250123,
            'label_lng' => 122.9635123,
        ]);
    }

    public function test_resident_cannot_update_overlay_settings(): void
    {
        $resident = Resident::create([
            'name' => 'Resident Test',
            'email' => 'resident_overlay@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withSession(['resident_id' => $resident->id, 'resident_name' => 'Resident Test'])
            ->withHeader('X-Portal', 'resident')
            ->putJson('/api/map/settings', [
                'overlay_opacity' => 0.5,
            ]);

        $response->assertUnauthorized();
    }

    public function test_resident_cannot_update_block_coordinates(): void
    {
        $resident = Resident::create([
            'name' => 'Resident Test',
            'email' => 'resident_blocks@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withSession(['resident_id' => $resident->id, 'resident_name' => 'Resident Test'])
            ->withHeader('X-Portal', 'resident')
            ->putJson('/api/map/blocks', [
                'blocks' => [
                    ['id' => 1, 'coordinates' => [[10.62, 122.96], [10.63, 122.96], [10.63, 122.97]]],
                ],
            ]);

        $response->assertUnauthorized();
    }

    public function test_resident_portal_renders_block_partition_toggle(): void
    {
        $html = file_get_contents(resource_path('views/layouts/residents.blade.php'));

        $this->assertStringContainsString('id="res-layer-blocks"', $html);
        $this->assertStringContainsString('id="res-filter-block"', $html);
        $this->assertStringContainsString('function renderResidentBlocks', $html);
        $this->assertStringContainsString('function toggleResidentBlocks', $html);
        $this->assertStringContainsString('function filterResidentBlocks', $html);
    }

    public function test_officer_portal_renders_block_partition_toggle_and_js_functions(): void
    {
        $view = file_get_contents(resource_path('views/layouts/officer-portal.blade.php'));
        $this->assertStringContainsString('id="officerLayerBlocks"', $view);
        $this->assertStringContainsString('id="officerFilterBlock"', $view);

        $js = file_get_contents(public_path('js/officer-portal.js'));
        $this->assertStringContainsString('function loadOfficerBlocks', $js);
        $this->assertStringContainsString('function renderOfficerBlocks', $js);
        $this->assertStringContainsString('function toggleOfficerBlocks', $js);
        $this->assertStringContainsString('function filterOfficerBlocks', $js);
    }

    public function test_all_portals_listen_for_realtime_block_updates(): void
    {
        $adminJs = file_get_contents(public_path('js/admin-dashboard.js'));
        $this->assertStringContainsString(".listen('.block'", $adminJs);
        $this->assertStringContainsString("addEventListener('block'", $adminJs);

        $officerJs = file_get_contents(public_path('js/officer-portal.js'));
        $this->assertStringContainsString(".listen('.block'", $officerJs);
        $this->assertStringContainsString("addEventListener('block'", $officerJs);

        $residentJs = file_get_contents(public_path('js/residents.js'));
        $this->assertStringContainsString(".listen('.block'", $residentJs);
        $this->assertStringContainsString("addEventListener('block'", $residentJs);
    }

    public function test_directional_starting_markers_and_simplified_block_labels_rendered(): void
    {
        $adminView = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));
        $this->assertStringContainsString('function getBlockStartInfo', $adminView);
        $this->assertStringContainsString('sub-start-dir-marker', $adminView);
        $this->assertStringContainsString('start-dir-arrow', $adminView);
        $this->assertStringContainsString('start-dir-code', $adminView);
        $this->assertStringContainsString('sub-start-popup', $adminView);
        $this->assertStringContainsString('fillOpacity: isFiltered ? 0.03 : (isBlock3 ? 0.08 : 0.18)', $adminView);

        $officerJs = file_get_contents(public_path('js/officer-portal.js'));
        $this->assertStringContainsString('function getBlockStartInfo', $officerJs);
        $this->assertStringContainsString('sub-start-dir-marker', $officerJs);

        $residentView = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringContainsString('function getBlockStartInfo', $residentView);
        $this->assertStringContainsString('sub-start-dir-marker', $residentView);

        $css = file_get_contents(public_path('css/ui-polish.css'));
        $this->assertStringContainsString('.sub-start-dir-marker', $css);
        $this->assertStringContainsString('.sub-start-dir-marker.is-sample', $css);
        $this->assertStringContainsString('.start-dir-arrow', $css);
        $this->assertStringContainsString('.start-dir-code', $css);
        $this->assertStringContainsString('.sub-start-popup', $css);

        $this->assertStringContainsString('sub-start-dir-marker is-sample', $adminView);
        $this->assertStringContainsString('sub-start-dir-marker is-sample', $residentView);
    }

    public function test_individual_household_lot_pins_are_omitted_from_subdivision_map(): void
    {
        $adminView = file_get_contents(resource_path('views/layouts/admin-dashboard.blade.php'));

        // Household layer toggle is removed from toolbar
        $this->assertStringNotContainsString('id="layer-households"', $adminView);
        // Active/vacant household legend dots are removed
        $this->assertStringNotContainsString('Household (Active)', $adminView);
        $this->assertStringNotContainsString('Household (Vacant)', $adminView);
        // renderFilteredHouseholds clears layers without plotting individual lot pins
        $this->assertStringContainsString('layerHouseholds.clearLayers()', $adminView);
        $this->assertStringNotContainsString('Drag pin to adjust lot position', $adminView);

        $residentView = file_get_contents(resource_path('views/layouts/residents.blade.php'));
        $this->assertStringNotContainsString('My Household', $residentView);
    }
}
