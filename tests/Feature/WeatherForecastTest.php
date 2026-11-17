<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherForecastTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Officer Anita',
            'email' => 'anita@example.test',
            'password' => 'Secret@12345',
            'status' => 'Active',
        ]);
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => 'Secret@12345',
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/weather')->assertUnauthorized();
    }

    public function test_resident_can_fetch_weather_forecast(): void
    {
        Cache::forget('subsync_weather_forecast');
        Http::fake([
            'wttr.in/*' => Http::response([
                'current_condition' => [
                    [
                        'temp_C' => '28',
                        'weatherDesc' => [['value' => 'Partly Cloudy']],
                        'weatherCode' => '116',
                    ],
                ],
            ], 200),
        ]);

        $res = $this->actingAs($this->resident(), 'resident')
            ->getJson('/api/weather')
            ->assertOk()
            ->assertJsonStructure([
                'temperature',
                'temp_display',
                'condition',
                'icon',
                'display',
                'source',
            ]);

        $this->assertSame(28, $res->json('temperature'));
        $this->assertSame('Partly Cloudy', $res->json('condition'));
        $this->assertSame('28°C Partly Cloudy', $res->json('display'));
    }

    public function test_officer_and_admin_can_fetch_weather_forecast(): void
    {
        Cache::forget('subsync_weather_forecast');
        Http::fake([
            'wttr.in/*' => Http::response([
                'current_condition' => [
                    [
                        'temp_C' => '30',
                        'weatherDesc' => [['value' => 'Clear']],
                        'weatherCode' => '113',
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($this->officer(), 'officer')
            ->getJson('/api/weather')
            ->assertOk();

        Cache::forget('subsync_weather_forecast');
        $this->actingAs($this->admin(), 'admin')
            ->getJson('/api/weather')
            ->assertOk();
    }

    public function test_weather_maps_rainy_conditions_correctly(): void
    {
        Cache::forget('subsync_weather_forecast');
        Http::fake([
            'wttr.in/*' => Http::response([
                'current_condition' => [
                    [
                        'temp_C' => '25',
                        'weatherDesc' => [['value' => 'Patchy light rain']],
                        'weatherCode' => '176',
                    ],
                ],
            ], 200),
        ]);

        $res = $this->actingAs($this->resident(), 'resident')
            ->getJson('/api/weather')
            ->assertOk();

        $this->assertSame(25, $res->json('temperature'));
        $this->assertSame('Light Rain', $res->json('condition'));
        $this->assertSame('cloud-rain', $res->json('icon'));
    }

    public function test_weather_falls_back_gracefully_when_service_fails(): void
    {
        Cache::forget('subsync_weather_forecast');
        Http::fake([
            'wttr.in/*' => Http::response('Gateway Timeout', 504),
        ]);

        $res = $this->actingAs($this->resident(), 'resident')
            ->getJson('/api/weather')
            ->assertOk();

        $this->assertSame('fallback', $res->json('source'));
        $this->assertNotEmpty($res->json('display'));
        $this->assertNotEmpty($res->json('icon'));
    }
}
