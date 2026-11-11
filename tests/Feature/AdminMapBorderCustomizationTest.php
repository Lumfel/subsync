<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MapSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapBorderCustomizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_freely_adjustable_border_controls(): void
    {
        $view = view('layouts.admin-dashboard', [
            'adminName' => 'Super Admin',
            'adminId' => 1,
            'prefs' => [],
            'role' => 'admin',
        ])->render();

        $this->assertStringContainsString('id="btn-customize-area"', $view);
        $this->assertStringContainsString('id="map-area-toolbar"', $view);
        $this->assertStringContainsString('id="btn-draw-area"', $view);
        $this->assertStringContainsString('id="btn-save-area"', $view);
        $this->assertStringContainsString('id="btn-cancel-area"', $view);
        $this->assertStringContainsString('id="btn-reset-area"', $view);
        $this->assertStringContainsString('id="map-customize-hint"', $view);

        // Verify key functions for mouse/cursor manipulation are present
        $this->assertStringContainsString('function enterCustomizeArea', $view);
        $this->assertStringContainsString('function createAreaHandles', $view);
        $this->assertStringContainsString('function updateAreaRectAndHandles', $view);
        $this->assertStringContainsString('function toggleDrawAreaMode', $view);
        $this->assertStringContainsString('function cancelCustomizeArea', $view);
        $this->assertStringContainsString('function saveMapArea', $view);
        $this->assertStringContainsString('function resetMapArea', $view);
    }

    public function test_admin_css_contains_map_area_handle_classes(): void
    {
        $css = file_get_contents(public_path('css/admin-dashboard.css'));

        $this->assertStringContainsString('.area-handle', $css);
        $this->assertStringContainsString('.area-handle-corner', $css);
        $this->assertStringContainsString('.area-handle-edge-ns', $css);
        $this->assertStringContainsString('.area-handle-edge-ew', $css);
        $this->assertStringContainsString('.area-handle-center', $css);
        $this->assertStringContainsString('.area-drawing-active', $css);
    }

    public function test_admin_can_update_freely_adjusted_border_bounds(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_map@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $customPayload = [
            'min_lat' => 10.6200000,
            'max_lat' => 10.6255000,
            'min_lng' => 122.9590000,
            'max_lng' => 122.9645000,
            'center_lat' => 10.6227500,
            'center_lng' => 122.9617500,
            'zoom' => 17,
        ];

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin Test'])
            ->putJson('/api/map/settings', $customPayload)
            ->assertOk();

        $res->assertJsonFragment(['success' => true]);

        $current = MapSetting::current();
        $this->assertEqualsWithDelta(10.6200000, $current->min_lat, 0.000001);
        $this->assertEqualsWithDelta(10.6255000, $current->max_lat, 0.000001);
        $this->assertEqualsWithDelta(122.9590000, $current->min_lng, 0.000001);
        $this->assertEqualsWithDelta(122.9645000, $current->max_lng, 0.000001);
    }
}
