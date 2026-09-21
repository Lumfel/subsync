<?php

namespace Database\Seeders;

use App\Models\HoaPolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HoaPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample HOA policies for resident reference
        HoaPolicy::create([
            'title' => 'Architectural Review Guidelines',
            'description' => 'All exterior modifications to homes, including paint colors, fences, additions, and landscaping changes, must be submitted to the Architectural Review Committee for approval before work begins.',
            'category' => 'Architectural Standards',
            'effective_date' => '2024-01-01',
            'status' => 'Active'
        ]);

        HoaPolicy::create([
            'title' => 'Pet Policy and Regulations',
            'description' => 'Pets must be leashed in common areas, waste must be cleaned up immediately, and excessive barking is prohibited. Certain breeds may have additional restrictions.',
            'category' => 'Community Rules',
            'effective_date' => '2024-03-15',
            'status' => 'Active'
        ]);

        HoaPolicy::create([
            'title' => 'Noise Ordinance and Quiet Hours',
            'description' => 'Quiet hours are from 10:00 PM to 7:00 AM daily. Loud music, construction noise, and disturbances that affect neighbors are prohibited during these hours.',
            'category' => 'Community Rules',
            'effective_date' => '2024-01-01',
            'status' => 'Active'
        ]);

        HoaPolicy::create([
            'title' => 'Parking Regulations and Enforcement',
            'description' => 'Street parking is limited to 24 hours. Vehicles must be parked in driveways or garages when possible. No commercial vehicles, RVs, or trailers may be parked on streets overnight.',
            'category' => 'Parking and Traffic',
            'effective_date' => '2024-05-01',
            'status' => 'Active'
        ]);

        HoaPolicy::create([
            'title' => 'Use of Common Areas and Amenities',
            'description' => 'Pool, clubhouse, parks, and other common areas are for resident use only. Guests must be accompanied by a resident. Proper attire and behavior are required at all times.',
            'category' => 'Amenities Usage',
            'effective_date' => '2024-02-01',
            'status' => 'Active'
        ]);

        HoaPolicy::create([
            'title' => 'Holiday Decoration Guidelines',
            'description' => 'Holiday decorations may be displayed from November 15 through January 15. Lights must be turned off by 11:00 PM. No decorations that obstruct walkways or create safety hazards.',
            'category' => 'Seasonal Guidelines',
            'effective_date' => '2024-10-01',
            'status' => 'Active'
        ]);
    }
}
