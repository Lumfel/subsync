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
        $policies = [
            [
                'title' => 'Architectural Review Guidelines',
                'description' => 'All exterior modifications to homes, including paint colors, fences, additions, and landscaping changes, must be submitted to the Architectural Review Committee for approval before work begins.',
                'category' => 'Architectural Standards',
                'effective_date' => '2024-01-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Pet Policy and Regulations',
                'description' => 'Pets must be leashed in common areas, waste must be cleaned up immediately, and excessive barking is prohibited. Maximum of 2 registered pets per household.',
                'category' => 'Community Rules',
                'effective_date' => '2024-03-15',
                'status' => 'Active',
            ],
            [
                'title' => 'Noise Ordinance and Quiet Hours',
                'description' => 'Quiet hours are from 10:00 PM to 7:00 AM daily. Loud music, videoke, construction noise, and disturbances that affect neighbors are prohibited during these hours.',
                'category' => 'Community Rules',
                'effective_date' => '2024-01-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Parking Regulations and Enforcement',
                'description' => 'Street parking is strictly limited to 24 hours. Vehicles must be parked in designated driveways or garages when possible. Commercial trucks and heavy equipment may not park overnight.',
                'category' => 'Parking and Traffic',
                'effective_date' => '2024-05-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Use of Common Areas and Amenities',
                'description' => 'Pool, clubhouse, basketball court, and parks are for resident use. Guests must be accompanied by a resident. Commercial photography or large gatherings require prior reservation.',
                'category' => 'Amenities Usage',
                'effective_date' => '2024-02-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Holiday Decoration Guidelines',
                'description' => 'Holiday decorations may be displayed from November 15 through January 15. Decorative lights must be turned off by 11:00 PM. No decorations that obstruct walkways or create fire hazards.',
                'category' => 'Seasonal Guidelines',
                'effective_date' => '2024-10-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Solid Waste Management & Segregation Policy',
                'description' => 'Mandatory segregation into Biodegradable, Non-Biodegradable, and Recyclable waste. Waste must only be brought out on designated collection mornings (Tuesdays and Fridays).',
                'category' => 'Environmental Guidelines',
                'effective_date' => '2024-06-01',
                'status' => 'Active',
            ],
            [
                'title' => 'Subdivision Speed Limit & Road Safety Protocol',
                'description' => 'The maximum vehicular speed limit within all subdivision streets is 20 km/h. Delivery couriers, motorcycles, and resident vehicles violating this will be subject to security notice.',
                'category' => 'Parking and Traffic',
                'effective_date' => '2024-04-15',
                'status' => 'Active',
            ],
            [
                'title' => 'Gate Security & Visitor Verification Protocol',
                'description' => 'All visitors and service providers must surrender a valid government ID at the guardhouse before entry. Residents expecting couriers or visitors are encouraged to pre-notify security.',
                'category' => 'Security Guidelines',
                'effective_date' => '2024-01-15',
                'status' => 'Active',
            ],
            [
                'title' => 'Tree Trimming & Property Boundary Maintenance',
                'description' => 'Overhanging tree branches that cross lot boundaries or encroach onto public streets, sidewalks, or electrical lines must be trimmed by the property owner within 14 days of notice.',
                'category' => 'Maintenance Standards',
                'effective_date' => '2024-07-01',
                'status' => 'Active',
            ],
        ];

        foreach ($policies as $policy) {
            HoaPolicy::firstOrCreate(
                ['title' => $policy['title']],
                $policy
            );
        }
    }
}
