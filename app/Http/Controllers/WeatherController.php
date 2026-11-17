<?php

namespace App\Http\Controllers;

use App\Models\MapSetting;
use App\Support\Tls;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * GET /api/weather
     * Returns the live weather forecast for the subdivision coordinates,
     * cached for 30 minutes to preserve fast page responsiveness.
     */
    public function current(): JsonResponse
    {
        $payload = Cache::remember('subsync_weather_forecast', 1800, function () {
            return $this->fetchForecast();
        });

        return response()->json($payload);
    }

    /**
     * Fetches current weather from wttr.in or returns a time-aware fallback.
     *
     * @return array{temperature: int, temp_display: string, condition: string, icon: string, display: string, source: string, updated_at: string}
     */
    public function fetchForecast(): array
    {
        $timezone = config('app.timezone', 'Asia/Manila');
        $hour = (int) now()->timezone($timezone)->format('H');
        $isDay = ($hour >= 6 && $hour < 18);

        $map = MapSetting::first();
        $lat = round((float) ($map?->center_lat ?: 10.6226), 4);
        $lng = round((float) ($map?->center_lng ?: 122.9613), 4);

        try {
            $options = ['timeout' => 3];
            $ca = Tls::caBundle();
            if ($ca) {
                $options['verify'] = $ca;
            }

            $res = Http::withOptions($options)->get("https://wttr.in/{$lat},{$lng}?format=j1");

            if ($res->successful()) {
                $data = $res->json();
                $current = $data['current_condition'][0] ?? null;

                if ($current) {
                    $tempC = (int) round((float) ($current['temp_C'] ?? ($isDay ? 29 : 27)));
                    $rawDesc = trim($current['weatherDesc'][0]['value'] ?? 'Partly Cloudy');
                    $code = (int) ($current['weatherCode'] ?? 116);

                    [$condition, $icon] = $this->mapCondition($rawDesc, $code, $isDay);

                    return [
                        'temperature' => $tempC,
                        'temp_display' => "{$tempC}°C",
                        'condition' => $condition,
                        'icon' => $icon,
                        'display' => "{$tempC}°C {$condition}",
                        'source' => 'live',
                        'updated_at' => now()->toISOString(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silently fall through to the graceful fallback
        }

        $fallbackTemp = $isDay ? 29 : 27;
        $fallbackCond = 'Partly Cloudy';
        $fallbackIcon = $isDay ? 'cloud-sun' : 'moon';

        return [
            'temperature' => $fallbackTemp,
            'temp_display' => "{$fallbackTemp}°C",
            'condition' => $fallbackCond,
            'icon' => $fallbackIcon,
            'display' => "{$fallbackTemp}°C {$fallbackCond}",
            'source' => 'fallback',
            'updated_at' => now()->toISOString(),
        ];
    }

    /**
     * Map raw description and WMO/wttr code to a clean label and icon name.
     *
     * @return array{0: string, 1: string}
     */
    private function mapCondition(string $rawDesc, int $code, bool $isDay): array
    {
        $lower = strtolower($rawDesc);

        // Thunderstorm
        if (in_array($code, [200, 386, 389, 392, 395], true) || str_contains($lower, 'thunder')) {
            return ['Thunderstorm', 'cloud-lightning'];
        }

        // Rain / Drizzle / Showers
        if (in_array($code, [176, 263, 266, 281, 284, 293, 296, 299, 302, 305, 308, 311, 314, 353, 356, 359], true)
            || str_contains($lower, 'rain')
            || str_contains($lower, 'drizzle')
            || str_contains($lower, 'shower')) {
            $label = str_contains($lower, 'light')
                ? 'Light Rain'
                : (str_contains($lower, 'shower') ? 'Rain Showers' : 'Rain');

            return [$label, 'cloud-rain'];
        }

        // Overcast / Cloudy
        if (in_array($code, [119, 122], true) || str_contains($lower, 'overcast') || ($lower === 'cloudy')) {
            return [str_contains($lower, 'overcast') ? 'Overcast' : 'Cloudy', 'cloud'];
        }

        // Partly cloudy
        if ($code === 116 || str_contains($lower, 'partly')) {
            return ['Partly Cloudy', $isDay ? 'cloud-sun' : 'cloud'];
        }

        // Clear / Sunny
        if ($code === 113 || str_contains($lower, 'clear') || str_contains($lower, 'sunny')) {
            return [$isDay ? 'Sunny' : 'Clear', $isDay ? 'sun' : 'moon'];
        }

        return [ucwords($lower), $isDay ? 'sun' : 'moon'];
    }
}
