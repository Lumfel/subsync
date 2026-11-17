<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Facility;
use App\Models\FinancialRecord;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\IssueReport;
use App\Models\IssueResponse;
use App\Models\Message;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Recommendation;
use App\Models\Resident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ==========================================
        // 1. ADMIN ACCOUNT
        // ==========================================
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@subsync.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin@12345'),
                'status' => 'Active',
            ]
        );

        // ==========================================
        // 2. HOA OFFICERS
        // ==========================================
        $officer1 = Officer::firstOrCreate(
            ['email' => 'officer1@subsync.com'],
            [
                'name' => 'Juan dela Cruz',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09171234567',
                'status' => 'Active',
                'role_description' => 'Security Officer',
            ]
        );

        $officer2 = Officer::firstOrCreate(
            ['email' => 'officer2@subsync.com'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09271234567',
                'status' => 'Active',
                'role_description' => 'Community Liaison',
            ]
        );

        $officer3 = Officer::firstOrCreate(
            ['email' => 'officer3@subsync.com'],
            [
                'name' => 'Ricardo Dalisay',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09371234567',
                'status' => 'Active',
                'role_description' => 'Maintenance Supervisor',
            ]
        );

        $officer4 = Officer::firstOrCreate(
            ['email' => 'officer4@subsync.com'],
            [
                'name' => 'Danilo Ramos',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09471234567',
                'status' => 'Active',
                'role_description' => 'HOA Secretary',
            ]
        );

        $officer5 = Officer::firstOrCreate(
            ['email' => 'officer5@subsync.com'],
            [
                'name' => 'Elena Bautista',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09571234567',
                'status' => 'Active',
                'role_description' => 'HOA Treasurer',
            ]
        );

        // PENDING OFFICER (For demonstrating live Admin approval of officer registration during defense)
        $officerPending = Officer::firstOrCreate(
            ['email' => 'officer_pending@subsync.com'],
            [
                'name' => 'Jaime Mercado',
                'password' => Hash::make('Officer@123'),
                'contact_number' => '09671234567',
                'status' => 'Pending',
                'role_description' => 'Safety & Health Officer',
            ]
        );

        // ==========================================
        // 3. HOUSEHOLDS (12 households in Terra Nova bounds)
        // ==========================================
        $householdsData = [
            ['block_lot_number' => 'Blk 1 Lot 5',  'status' => 'Occupied', 'latitude' => 10.6230, 'longitude' => 122.9610],
            ['block_lot_number' => 'Blk 2 Lot 3',  'status' => 'Occupied', 'latitude' => 10.6235, 'longitude' => 122.9620],
            ['block_lot_number' => 'Blk 3 Lot 8',  'status' => 'Vacant',   'latitude' => 10.6220, 'longitude' => 122.9605],
            ['block_lot_number' => 'Blk 4 Lot 2',  'status' => 'Occupied', 'latitude' => 10.6240, 'longitude' => 122.9615],
            ['block_lot_number' => 'Blk 5 Lot 10', 'status' => 'Occupied', 'latitude' => 10.6225, 'longitude' => 122.9630],
            ['block_lot_number' => 'Blk 6 Lot 1',  'status' => 'Occupied', 'latitude' => 10.6218, 'longitude' => 122.9612],
            ['block_lot_number' => 'Blk 7 Lot 4',  'status' => 'Occupied', 'latitude' => 10.6242, 'longitude' => 122.9628],
            ['block_lot_number' => 'Blk 8 Lot 9',  'status' => 'Vacant',   'latitude' => 10.6232, 'longitude' => 122.9632],
            ['block_lot_number' => 'Blk 9 Lot 12', 'status' => 'Occupied', 'latitude' => 10.6228, 'longitude' => 122.9602],
            ['block_lot_number' => 'Blk 10 Lot 7', 'status' => 'Occupied', 'latitude' => 10.6215, 'longitude' => 122.9622],
            ['block_lot_number' => 'Blk 11 Lot 15', 'status' => 'Occupied', 'latitude' => 10.6238, 'longitude' => 122.9605],
            ['block_lot_number' => 'Blk 12 Lot 6', 'status' => 'Occupied', 'latitude' => 10.6244, 'longitude' => 122.9618],
        ];

        $households = [];
        foreach ($householdsData as $h) {
            $households[$h['block_lot_number']] = Household::firstOrCreate(
                ['block_lot_number' => $h['block_lot_number']],
                ['status' => $h['status'], 'latitude' => $h['latitude'], 'longitude' => $h['longitude']]
            );
        }

        // ==========================================
        // 4. RESIDENTS (12 test accounts, Resident@123)
        // ==========================================
        $residentsData = [
            [
                'email' => 'resident1@subsync.com',
                'name' => 'Pedro Reyes',
                'house_id' => $households['Blk 1 Lot 5']->id,
                'contact_number' => '09181234567',
                'status' => 'Active',
                'current_balance' => 500.00,
            ],
            [
                'email' => 'resident2@subsync.com',
                'name' => 'Ana Garcia',
                'house_id' => $households['Blk 2 Lot 3']->id,
                'contact_number' => '09281234567',
                'status' => 'Active',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident3@subsync.com',
                'name' => 'Carlos Bautista',
                'house_id' => $households['Blk 3 Lot 8']->id,
                'contact_number' => '09381234567',
                'status' => 'Non-Compliant',
                'current_balance' => 2500.00,
            ],
            [
                'email' => 'resident4@subsync.com',
                'name' => 'Eduardo Cruz',
                'house_id' => $households['Blk 4 Lot 2']->id,
                'contact_number' => '09481234567',
                'status' => 'Active',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident5@subsync.com',
                'name' => 'Maria Elena Santos',
                'house_id' => $households['Blk 5 Lot 10']->id,
                'contact_number' => '09581234567',
                'status' => 'Active',
                'current_balance' => 500.00,
            ],
            [
                'email' => 'resident6@subsync.com',
                'name' => 'Roberto Tan',
                'house_id' => $households['Blk 6 Lot 1']->id,
                'contact_number' => '09681234567',
                'status' => 'Active',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident7@subsync.com',
                'name' => 'Clarisse Ramos',
                'house_id' => $households['Blk 7 Lot 4']->id,
                'contact_number' => '09781234567',
                'status' => 'Active',
                'current_balance' => 1000.00,
            ],
            [
                'email' => 'resident8@subsync.com',
                'name' => 'Joshua Dizon',
                'house_id' => $households['Blk 8 Lot 9']->id,
                'contact_number' => '09881234567',
                'status' => 'Active',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident9@subsync.com',
                'name' => 'Katrina Villanueva',
                'house_id' => $households['Blk 9 Lot 12']->id,
                'contact_number' => '09981234567',
                'status' => 'Active',
                'current_balance' => 500.00,
            ],
            [
                'email' => 'resident10@subsync.com',
                'name' => 'Antonio Morales',
                'house_id' => $households['Blk 10 Lot 7']->id,
                'contact_number' => '09191234567',
                'status' => 'Active',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident11@subsync.com',
                'name' => 'Grace Alcantara',
                'house_id' => $households['Blk 11 Lot 15']->id,
                'contact_number' => '09291234567',
                'status' => 'Pending',
                'current_balance' => 0.00,
            ],
            [
                'email' => 'resident12@subsync.com',
                'name' => 'Felipe Guanzon',
                'house_id' => $households['Blk 12 Lot 6']->id,
                'contact_number' => '09391234567',
                'status' => 'Pending',
                'current_balance' => 0.00,
            ],
        ];

        $residents = [];
        foreach ($residentsData as $r) {
            $residents[$r['email']] = Resident::firstOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'password' => Hash::make('Resident@123'),
                    'house_id' => $r['house_id'],
                    'contact_number' => $r['contact_number'],
                    'status' => $r['status'],
                    'current_balance' => $r['current_balance'],
                ]
            );
        }

        // ==========================================
        // 5. HOUSEHOLD MEMBERS (Family members)
        // ==========================================
        $membersData = [
            ['house_id' => $households['Blk 1 Lot 5']->id,  'name' => 'Lina Reyes',       'relationship' => 'Spouse', 'contact_number' => '09111111111'],
            ['house_id' => $households['Blk 1 Lot 5']->id,  'name' => 'Ricky Reyes',      'relationship' => 'Child',  'contact_number' => null],
            ['house_id' => $households['Blk 2 Lot 3']->id,  'name' => 'Bert Garcia',      'relationship' => 'Spouse', 'contact_number' => '09222222222'],
            ['house_id' => $households['Blk 2 Lot 3']->id,  'name' => 'Sofia Garcia',     'relationship' => 'Child',  'contact_number' => null],
            ['house_id' => $households['Blk 4 Lot 2']->id,  'name' => 'Teresa Cruz',      'relationship' => 'Spouse', 'contact_number' => '09444444444'],
            ['house_id' => $households['Blk 4 Lot 2']->id,  'name' => 'Mark Cruz',        'relationship' => 'Child',  'contact_number' => '09445555555'],
            ['house_id' => $households['Blk 5 Lot 10']->id, 'name' => 'Ramon Santos',     'relationship' => 'Spouse', 'contact_number' => '09555555555'],
            ['house_id' => $households['Blk 6 Lot 1']->id,  'name' => 'Angela Tan',       'relationship' => 'Spouse', 'contact_number' => '09666666666'],
            ['house_id' => $households['Blk 6 Lot 1']->id,  'name' => 'Brian Tan',        'relationship' => 'Child',  'contact_number' => null],
            ['house_id' => $households['Blk 7 Lot 4']->id,  'name' => 'Dennis Ramos',     'relationship' => 'Spouse', 'contact_number' => '09777777777'],
            ['house_id' => $households['Blk 9 Lot 12']->id, 'name' => 'Miguel Villanueva', 'relationship' => 'Spouse', 'contact_number' => '09999999999'],
            ['house_id' => $households['Blk 9 Lot 12']->id, 'name' => 'Chloe Villanueva', 'relationship' => 'Child',  'contact_number' => null],
            ['house_id' => $households['Blk 10 Lot 7']->id, 'name' => 'Patricia Morales', 'relationship' => 'Spouse', 'contact_number' => '09199999999'],
            ['house_id' => $households['Blk 11 Lot 15']->id, 'name' => 'Dominic Alcantara', 'relationship' => 'Spouse', 'contact_number' => '09299999999'],
            ['house_id' => $households['Blk 12 Lot 6']->id, 'name' => 'Andrea Guanzon',   'relationship' => 'Spouse', 'contact_number' => '09399999999'],
        ];

        foreach ($membersData as $m) {
            HouseholdMember::firstOrCreate(
                ['house_id' => $m['house_id'], 'name' => $m['name']],
                ['relationship' => $m['relationship'], 'contact_number' => $m['contact_number']]
            );
        }

        // ==========================================
        // 6. ANNOUNCEMENTS (12 announcements: 10 Active, 2 Draft)
        // ==========================================
        $announcementsData = [
            [
                'admin_id' => $admin->id,
                'officer_id' => null,
                'title' => 'Monthly Dues Reminder - June 2026',
                'content' => 'Kindly settle your monthly HOA dues before June 30, 2026. Electronic payment via GCash is now available directly through your resident portal.',
                'tag' => 'Finance',
                'status' => 'Active',
            ],
            [
                'admin_id' => $admin->id,
                'officer_id' => null,
                'title' => 'Road Resurfacing & Paving Schedule',
                'content' => 'Main avenue asphalt resurfacing is scheduled for June 22-25. Light vehicles may take the perimeter route through Block 6.',
                'tag' => 'Maintenance',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer1->id,
                'title' => 'Barangay & HOA General Assembly',
                'content' => 'Annual community general assembly on June 28 at 8:30 AM at the Multi-Purpose Hall. Agenda includes fiscal report and board elections.',
                'tag' => 'Community',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer2->id,
                'title' => 'Free Anti-Rabies Vaccination & Pet Registration',
                'content' => 'City Veterinary Office will conduct free anti-rabies vaccinations at the Clubhouse on July 5 from 9:00 AM to 3:00 PM. Please bring pet records.',
                'tag' => 'Health',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer2->id,
                'title' => 'Terra Nova Summer Basketball Tournament 2026',
                'content' => 'Registration for the Inter-Block Summer League is officially open! Team rosters must be submitted to Officer Juan dela Cruz by June 20.',
                'tag' => 'Sports',
                'status' => 'Active',
            ],
            [
                'admin_id' => $admin->id,
                'officer_id' => null,
                'title' => 'Mandatory Solid Waste Segregation Guidelines',
                'content' => 'Strict segregation of biodegradable and non-biodegradable waste is enforced. Unsegregated garbage will not be collected by city haulers.',
                'tag' => 'Environment',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer1->id,
                'title' => 'Perimeter Security Advisory & Visitor RFID Rollout',
                'content' => 'Security personnel will conduct stricter evening verification. Vehicle RFID tag applications are now available at the Administration Office.',
                'tag' => 'Security',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer3->id,
                'title' => 'Swimming Pool Sanitation & Maintenance Schedule',
                'content' => 'The community swimming pool will undergo deep chemical balancing and filter maintenance every Monday. Pool hours remain Tue-Sun 7AM-9PM.',
                'tag' => 'Amenities',
                'status' => 'Active',
            ],
            [
                'admin_id' => $admin->id,
                'officer_id' => null,
                'title' => 'Water Main Valve Preventive Inspection',
                'content' => 'Temporary water pressure reduction may occur on Thursday from 1:00 PM to 4:00 PM due to mainline pressure testing. Please store adequate water.',
                'tag' => 'Notice',
                'status' => 'Active',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer1->id,
                'title' => 'Quiet Hours & Subdivision Curfew Notice',
                'content' => 'Quiet hours are strictly observed starting at 10:00 PM daily. Videoke and loud audio systems must be turned off to preserve community peace.',
                'tag' => 'Notice',
                'status' => 'Active',
            ],
            [
                'admin_id' => $admin->id,
                'officer_id' => null,
                'title' => '[DRAFT] Proposed Solar Perimeter Lighting Project',
                'content' => 'Draft proposal outlining installation of 25 standalone solar LED streetlights along the perimeter fence to improve nighttime security.',
                'tag' => 'Notice',
                'status' => 'Draft',
            ],
            [
                'admin_id' => null,
                'officer_id' => $officer2->id,
                'title' => '[DRAFT] Clubhouse Facility Rental Tariff Revision',
                'content' => 'Draft proposal for updated reservation fees and security deposit policies for private resident bookings of the Multi-Purpose Hall.',
                'tag' => 'Finance',
                'status' => 'Draft',
            ],
        ];

        foreach ($announcementsData as $a) {
            Announcement::firstOrCreate(
                ['title' => $a['title']],
                $a
            );
        }

        // ==========================================
        // 7. ISSUE REPORTS (12 realistic issues with assignments & SLA due dates)
        // ==========================================
        $issuesData = [
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'category' => 'Infrastructure',
                'title' => 'Broken Street Light near Lot 5',
                'description' => 'Street light post near Blk 1 Lot 5 has been flickering and completely went dark 3 days ago. Street is dangerously dim at night.',
                'status' => 'In Progress',
                'priority' => 'High',
                'latitude' => 10.6231,
                'longitude' => 122.9611,
                'assigned_officer_id' => $officer3->id,
                'due_date' => now()->addDays(1)->format('Y-m-d'),
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident2@subsync.com']->id,
                'category' => 'Noise',
                'title' => 'Loud Music Past Midnight',
                'description' => 'Neighbor plays high-volume music and videoke past 12:30 AM on weekday nights, violating subdivision quiet hours.',
                'status' => 'Resolved',
                'priority' => 'Low',
                'latitude' => 10.6236,
                'longitude' => 122.9621,
                'assigned_officer_id' => $officer1->id,
                'due_date' => now()->subDays(1)->format('Y-m-d'),
                'resolved_at' => now()->subDay(),
            ],
            [
                'resident_id' => $residents['resident3@subsync.com']->id,
                'category' => 'Drainage',
                'title' => 'Clogged Canal along Main Road',
                'description' => 'Culvert near Blk 3 Lot 8 is choked with silt and dried leaves causing road flooding during moderate rain.',
                'status' => 'Resolved',
                'priority' => 'High',
                'latitude' => 10.6221,
                'longitude' => 122.9606,
                'assigned_officer_id' => $officer3->id,
                'due_date' => now()->subDays(2)->format('Y-m-d'),
                'resolved_at' => now()->subDays(2),
            ],
            [
                'resident_id' => $residents['resident4@subsync.com']->id,
                'category' => 'Security',
                'title' => 'Stray Dogs Roaming Near Playground',
                'description' => 'A pack of 4 stray dogs has been frequenting the children playground area during afternoons, posing a biting risk for kids.',
                'status' => 'In Progress',
                'priority' => 'Medium',
                'latitude' => 10.6239,
                'longitude' => 122.9603,
                'assigned_officer_id' => $officer1->id,
                'due_date' => now()->addDays(2)->format('Y-m-d'),
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident5@subsync.com']->id,
                'category' => 'Water Supply',
                'title' => 'Low Water Pressure During Peak Hours',
                'description' => 'Water pressure drops down to a trickle from 6:00 AM to 8:30 AM along Block 5 residences.',
                'status' => 'In Progress',
                'priority' => 'High',
                'latitude' => 10.6226,
                'longitude' => 122.9631,
                'assigned_officer_id' => $officer2->id,
                'due_date' => now()->addDays(2)->format('Y-m-d'),
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident6@subsync.com']->id,
                'category' => 'Waste Management',
                'title' => 'Uncollected Household Waste along Block 6',
                'description' => 'The collection truck skipped Block 6 during yesterday regular collection. Bins are beginning to attract stray animals.',
                'status' => 'Resolved',
                'priority' => 'Medium',
                'latitude' => 10.6219,
                'longitude' => 122.9613,
                'assigned_officer_id' => $officer2->id,
                'due_date' => now()->subDays(1)->format('Y-m-d'),
                'resolved_at' => now()->subDay(),
            ],
            [
                'resident_id' => $residents['resident7@subsync.com']->id,
                'category' => 'Road Hazard',
                'title' => 'Fallen Tree Branch Obstructing Roadway',
                'description' => 'Heavy thunderstorm caused a large acacia branch to snap and block one full vehicular lane along Block 7.',
                'status' => 'Resolved',
                'priority' => 'Critical',
                'latitude' => 10.6241,
                'longitude' => 122.9627,
                'assigned_officer_id' => $officer3->id,
                'due_date' => now()->subDays(3)->format('Y-m-d'),
                'resolved_at' => now()->subDays(3),
            ],
            [
                'resident_id' => $residents['resident8@subsync.com']->id,
                'category' => 'Amenities',
                'title' => 'Bent Rim at Basketball Court',
                'description' => 'The south basketball hoop rim is badly bent and net is torn from excessive dunking. Needs welding or rim replacement.',
                'status' => 'Resolved',
                'priority' => 'Low',
                'latitude' => 10.6233,
                'longitude' => 122.9609,
                'assigned_officer_id' => $officer3->id,
                'due_date' => now()->subDays(2)->format('Y-m-d'),
                'resolved_at' => now()->subDays(2),
            ],
            [
                'resident_id' => $residents['resident9@subsync.com']->id,
                'category' => 'Water Supply',
                'title' => 'Leaking Mainline Valve Near Fire Hydrant #3',
                'description' => 'Pressurized clean water is continuously bubbling out from under the asphalt pavement beside the hydrant near Lot 12.',
                'status' => 'In Progress',
                'priority' => 'Critical',
                'latitude' => 10.6227,
                'longitude' => 122.9604,
                'assigned_officer_id' => $officer3->id,
                'due_date' => now()->addDays(1)->format('Y-m-d'),
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident10@subsync.com']->id,
                'category' => 'Security',
                'title' => 'Delivery Motorcycles Speeding along Block 10',
                'description' => 'Commercial delivery riders are routinely speeding over 45 km/h along the residential avenue where children ride bicycles.',
                'status' => 'Pending',
                'priority' => 'Medium',
                'latitude' => 10.6216,
                'longitude' => 122.9621,
                'assigned_officer_id' => $officer1->id,
                'due_date' => now()->addDays(4)->format('Y-m-d'),
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'category' => 'Infrastructure',
                'title' => 'Asphalt Pothole Developing Near North Gate',
                'description' => 'A 2-foot wide pothole is deepening near the exit approach of the North Gate, scraping sedan undercarriages.',
                'status' => 'Pending',
                'priority' => 'Low',
                'latitude' => 10.6243,
                'longitude' => 122.9617,
                'assigned_officer_id' => null, // Unassigned for dispatch demo!
                'due_date' => null,
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident2@subsync.com']->id,
                'category' => 'Sanitation',
                'title' => 'Stagnant Rainwater Pooling in Vacant Lot',
                'description' => 'The vacant lot at Blk 3 Lot 8 has deep depressions collecting stagnant water after heavy rains, raising dengue mosquito concerns.',
                'status' => 'In Progress',
                'priority' => 'High',
                'latitude' => 10.6222,
                'longitude' => 122.9607,
                'assigned_officer_id' => null, // Unassigned for dispatch demo!
                'due_date' => null,
                'resolved_at' => null,
            ],
            [
                'resident_id' => $residents['resident3@subsync.com']->id,
                'category' => 'Other',
                'title' => 'Low-Hanging Telecom Cable Over Street',
                'description' => 'Non-general resident report: Unidentified overhead cable sagging low across the roadway between Block 8 and Block 9.',
                'status' => 'Pending',
                'priority' => 'Low',
                'latitude' => 10.6235,
                'longitude' => 122.9615,
                'assigned_officer_id' => null,
                'due_date' => null,
                'resolved_at' => null,
            ],
        ];

        $issues = [];
        foreach ($issuesData as $iss) {
            $issues[$iss['title']] = IssueReport::updateOrCreate(
                ['title' => $iss['title']],
                $iss
            );
        }

        // Seed sample staff responses for resolved / in-progress issues
        if (isset($issues['Broken Street Light near Lot 5'])) {
            IssueResponse::firstOrCreate(
                [
                    'issue_id' => $issues['Broken Street Light near Lot 5']->id,
                    'response_content' => 'Maintenance team dispatched. Electrician scheduled to replace LED driver on Tuesday morning.',
                ],
                [
                    'officer_id' => $officer3->id,
                    'responder_type' => 'Officer',
                ]
            );
        }

        if (isset($issues['Loud Music Past Midnight'])) {
            IssueResponse::firstOrCreate(
                [
                    'issue_id' => $issues['Loud Music Past Midnight']->id,
                    'response_content' => 'Security patrol dispatched at 12:45 AM. Household was given a verbal warning and immediately lowered the volume. Case resolved.',
                ],
                [
                    'officer_id' => $officer1->id,
                    'responder_type' => 'Officer',
                ]
            );
        }

        if (isset($issues['Fallen Tree Branch Obstructing Roadway'])) {
            IssueResponse::firstOrCreate(
                [
                    'issue_id' => $issues['Fallen Tree Branch Obstructing Roadway']->id,
                    'response_content' => 'Maintenance crew with chainsaws cleared the branch and debris within 45 minutes. Road is fully passable.',
                ],
                [
                    'admin_id' => $admin->id,
                    'responder_type' => 'Admin',
                ]
            );
        }

        // ==========================================
        // 8. COMMUNITY RECOMMENDATIONS (10 recommendations)
        // ==========================================
        $recommendationsData = [
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'title' => 'Install Solar Street Lights along Outer Perimeter',
                'description' => 'Requesting solar LED lights along the dark perimeter fence behind Blocks 1 and 2 to reduce grid electric bills and boost security.',
                'status' => 'Approved',
            ],
            [
                'resident_id' => $residents['resident2@subsync.com']->id,
                'title' => 'Establish Community Vegetable and Herb Garden',
                'description' => 'Suggesting converting the empty municipal easement near Central Park into a raised-bed community vegetable garden for residents.',
                'status' => 'Approved',
            ],
            [
                'resident_id' => $residents['resident4@subsync.com']->id,
                'title' => 'Rubberized Speed Bumps Near Children Playground',
                'description' => 'Install two rubber speed bumps along the avenue directly in front of the playground to enforce the 20 km/h speed limit.',
                'status' => 'Reviewed',
            ],
            [
                'resident_id' => $residents['resident5@subsync.com']->id,
                'title' => 'CCTV Coverage for North Secondary Gate',
                'description' => 'Add 2 high-definition night vision CCTV cameras connected to the main guardhouse monitoring station at the secondary pedestrian gate.',
                'status' => 'Approved',
            ],
            [
                'resident_id' => $residents['resident6@subsync.com']->id,
                'title' => 'Modern Heavy-Duty Swings for Playground',
                'description' => 'Replace the rusty swing chains with rubber-coated chains and add a toddler-safe bucket seat at the playground.',
                'status' => 'Reviewed',
            ],
            [
                'resident_id' => $residents['resident7@subsync.com']->id,
                'title' => 'Free Wi-Fi Access Point at the Multi-Purpose Hall',
                'description' => 'Provide guest Wi-Fi connection at the clubhouse and multi-purpose hall for students and attendees of community meetings.',
                'status' => 'Pending',
            ],
            [
                'resident_id' => $residents['resident8@subsync.com']->id,
                'title' => 'Segregated Recycling Bins at Central Park',
                'description' => 'Place color-coded bins for plastic bottles, cans, and paper near the basketball court and park benches.',
                'status' => 'Approved',
            ],
            [
                'resident_id' => $residents['resident9@subsync.com']->id,
                'title' => 'Covered Pedestrian Walkway from Guardhouse',
                'description' => 'Construct a canopy covered walkway from the main entrance guardhouse to Block 2 to shield commuting residents during rainy days.',
                'status' => 'Pending',
            ],
            [
                'resident_id' => $residents['resident10@subsync.com']->id,
                'title' => 'Automated RFID Barrier Arm for Residents',
                'description' => 'Upgrade the manual guardhouse boom barrier with an RFID reader so resident vehicles can enter smoothly during peak rush hours.',
                'status' => 'Reviewed',
            ],
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'title' => 'Subdivision Organic Composting Station',
                'description' => 'Set up an enclosed composting pit at the MRF to process grass clippings and tree trimmings into organic fertilizer for park landscaping.',
                'status' => 'Pending',
            ],
        ];

        foreach ($recommendationsData as $rec) {
            Recommendation::firstOrCreate(
                ['title' => $rec['title']],
                $rec
            );
        }

        // ==========================================
        // 9. FINANCIAL RECORDS (Ledger Entries)
        // ==========================================
        $financialRecordsData = [
            ['resident_id' => $residents['resident1@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident2@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident2@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00,  'description' => 'Monthly dues June 2026 - GCash Paid', 'record_date' => '2026-06-05'],
            ['resident_id' => $residents['resident3@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 2500.00, 'description' => 'Accumulated past dues Q1-Q2 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident4@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident4@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00,  'description' => 'Monthly dues June 2026 - GCash Paid', 'record_date' => '2026-06-08'],
            ['resident_id' => $residents['resident5@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident6@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident6@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00,  'description' => 'Monthly dues June 2026 - Cash Paid', 'record_date' => '2026-06-10'],
            ['resident_id' => $residents['resident7@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 1000.00, 'description' => 'Monthly dues May-June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident8@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident8@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00,  'description' => 'Monthly dues June 2026 - GCash Paid', 'record_date' => '2026-06-12'],
            ['resident_id' => $residents['resident9@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident10@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Due',     'amount' => 500.00,  'description' => 'Monthly dues June 2026', 'record_date' => '2026-06-01'],
            ['resident_id' => $residents['resident10@subsync.com']->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00,  'description' => 'Monthly dues June 2026 - GCash Paid', 'record_date' => '2026-06-14'],
        ];

        foreach ($financialRecordsData as $fr) {
            FinancialRecord::firstOrCreate(
                [
                    'resident_id' => $fr['resident_id'],
                    'record_type' => $fr['record_type'],
                    'description' => $fr['description'],
                    'record_date' => $fr['record_date'],
                ],
                $fr
            );
        }

        // ==========================================
        // 10. PAYMENT SUBMISSIONS (10 GCash Submissions)
        // ==========================================
        $paymentsData = [
            [
                'resident_id' => $residents['resident2@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260605-8901',
                'gcash_sender_name' => 'Ana Garcia',
                'gcash_number' => '09281234567',
                'paid_at' => '2026-06-05',
                'status' => 'Validated',
                'admin_response' => 'Payment verified and posted to resident ledger. Official receipt generated.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(10),
            ],
            [
                'resident_id' => $residents['resident4@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260608-4521',
                'gcash_sender_name' => 'Eduardo Cruz',
                'gcash_number' => '09481234567',
                'paid_at' => '2026-06-08',
                'status' => 'Validated',
                'admin_response' => 'Payment confirmed. Thank you!',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(8),
            ],
            [
                'resident_id' => $residents['resident6@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260610-7712',
                'gcash_sender_name' => 'Roberto Tan',
                'gcash_number' => '09681234567',
                'paid_at' => '2026-06-10',
                'status' => 'Validated',
                'admin_response' => 'Verified with bank statement.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(6),
            ],
            [
                'resident_id' => $residents['resident8@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260612-3390',
                'gcash_sender_name' => 'Joshua Dizon',
                'gcash_number' => '09881234567',
                'paid_at' => '2026-06-12',
                'status' => 'Validated',
                'admin_response' => 'Validated.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(4),
            ],
            [
                'resident_id' => $residents['resident10@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260614-1124',
                'gcash_sender_name' => 'Antonio Morales',
                'gcash_number' => '09191234567',
                'paid_at' => '2026-06-14',
                'status' => 'Validated',
                'admin_response' => 'Receipt cleared.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(2),
            ],
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260515-6601',
                'gcash_sender_name' => 'Pedro Reyes',
                'gcash_number' => '09181234567',
                'paid_at' => '2026-05-15',
                'status' => 'Validated',
                'admin_response' => 'May dues validated.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subMonth(),
            ],
            // PENDING VALIDATION (Ready for live demo validation!)
            [
                'resident_id' => $residents['resident1@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260616-9988',
                'gcash_sender_name' => 'Pedro Reyes',
                'gcash_number' => '09181234567',
                'paid_at' => '2026-06-16',
                'status' => 'Pending',
                'admin_response' => null,
            ],
            [
                'resident_id' => $residents['resident5@subsync.com']->id,
                'amount' => 500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260616-5544',
                'gcash_sender_name' => 'Maria Elena Santos',
                'gcash_number' => '09581234567',
                'paid_at' => '2026-06-16',
                'status' => 'Pending',
                'admin_response' => null,
            ],
            [
                'resident_id' => $residents['resident7@subsync.com']->id,
                'amount' => 1000.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260617-3321',
                'gcash_sender_name' => 'Clarisse Ramos',
                'gcash_number' => '09781234567',
                'paid_at' => '2026-06-17',
                'status' => 'Pending',
                'admin_response' => null,
            ],
            // REJECTED (Showing error / feedback workflow)
            [
                'resident_id' => $residents['resident3@subsync.com']->id,
                'amount' => 2500.00,
                'method' => 'gcash',
                'reference_no' => 'GCASH-20260610-0012',
                'gcash_sender_name' => 'Carlos Bautista',
                'gcash_number' => '09381234567',
                'paid_at' => '2026-06-10',
                'status' => 'Rejected',
                'rejection_reason' => 'Illegible receipt screenshot. Transaction reference did not match bank logs. Please re-upload a clear receipt.',
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(5),
            ],
        ];

        foreach ($paymentsData as $p) {
            PaymentSubmission::firstOrCreate(
                ['reference_no' => $p['reference_no']],
                $p
            );
        }

        // ==========================================
        // 11. FACILITIES (10 Map Facilities)
        // ==========================================
        $facilitiesData = [
            ['name' => 'Community Clubhouse',                    'description' => 'Main community clubhouse with administrative office, air-conditioned function rooms, and veranda.',   'latitude' => 10.6228, 'longitude' => 122.9614],
            ['name' => 'Outdoor Basketball Court',                'description' => 'Full-size covered court with electronic scoreboard, open daily 6:00 AM - 10:00 PM.',                   'latitude' => 10.6232, 'longitude' => 122.9608],
            ['name' => 'Main Entrance Gate & Guardhouse',         'description' => '24/7 security checkpoint, RFID resident lane, visitor registration booth, and emergency response post.', 'latitude' => 10.6222, 'longitude' => 122.9625],
            ['name' => 'Children\'s Playground',                  'description' => 'Safe play facility with swings, spiral slides, rubber matting, and shaded park benches for parents.',    'latitude' => 10.6238, 'longitude' => 122.9602],
            ['name' => 'Multi-Purpose Hall',                      'description' => 'Community event hall available for resident assembly, sports tournaments, and private reservations.',    'latitude' => 10.6224, 'longitude' => 122.9619],
            ['name' => 'Community Swimming Pool',                 'description' => 'Semi-olympic lap pool and children wading pool with certified lifeguard on duty on weekends.',          'latitude' => 10.6229, 'longitude' => 122.9612],
            ['name' => 'Tennis & Pickleball Court',               'description' => 'Asphalt surface tennis and pickleball court equipped with nighttime floodlights.',                      'latitude' => 10.6235, 'longitude' => 122.9606],
            ['name' => 'North Perimeter Gate',                    'description' => 'Secondary access gate for pedestrian traffic and emergency vehicle entry during rush hours.',             'latitude' => 10.6245, 'longitude' => 122.9620],
            ['name' => 'Central Park & Gazebo',                   'description' => 'Landscaped open park with ornamental trees, jogging track, and shaded gazebo for community relaxation.',  'latitude' => 10.6233, 'longitude' => 122.9618],
            ['name' => 'Materials Recovery Facility (MRF)',       'description' => 'Community waste segregation station, composting facility, and recyclable material collection area.',       'latitude' => 10.6215, 'longitude' => 122.9610],
        ];

        foreach ($facilitiesData as $f) {
            Facility::firstOrCreate(
                ['name' => $f['name']],
                $f
            );
        }

        // ==========================================
        // 12. COMMUNITY EVENTS & RSVPS (4 Events)
        // ==========================================
        $eventsData = [
            [
                'title' => 'Annual HOA General Assembly & Board Election',
                'description' => 'Official meeting of all registered homeowners. Agenda includes 2026 fiscal audit report, proposed capital expenditures, and election of new board members.',
                'location' => 'Multi-Purpose Hall',
                'starts_at' => now()->addDays(7)->setTime(8, 30),
                'ends_at' => now()->addDays(7)->setTime(12, 0),
                'created_by_type' => 'admin',
                'created_by_id' => $admin->id,
            ],
            [
                'title' => 'Terra Nova Community Clean-Up & Tree Planting',
                'description' => 'Neighborhood greening initiative. We will plant 50 native saplings along the creek park. Snacks and gardening gloves provided.',
                'location' => 'Central Park & Gazebo',
                'starts_at' => now()->addDays(14)->setTime(7, 0),
                'ends_at' => now()->addDays(14)->setTime(10, 30),
                'created_by_type' => 'officer',
                'created_by_id' => $officer2->id,
            ],
            [
                'title' => 'Inter-Block Summer Basketball League Opening',
                'description' => 'Opening parade and opening matches for the 2026 Terra Nova Basketball tournament. All blocks are invited to support their teams.',
                'location' => 'Outdoor Basketball Court',
                'starts_at' => now()->addDays(21)->setTime(16, 0),
                'ends_at' => now()->addDays(21)->setTime(21, 0),
                'created_by_type' => 'officer',
                'created_by_id' => $officer1->id,
            ],
            [
                'title' => 'Disaster Preparedness & First Aid Training',
                'description' => 'Free community workshop conducted by the City CDRRMO on household earthquake safety, basic life support, and CPR certification.',
                'location' => 'Clubhouse Main Hall',
                'starts_at' => now()->addDays(28)->setTime(9, 0),
                'ends_at' => now()->addDays(28)->setTime(15, 0),
                'created_by_type' => 'admin',
                'created_by_id' => $admin->id,
            ],
        ];

        foreach ($eventsData as $ev) {
            $createdEvent = Event::firstOrCreate(
                ['title' => $ev['title']],
                $ev
            );

            // Seed sample RSVPs
            EventRsvp::firstOrCreate(
                ['event_id' => $createdEvent->id, 'resident_id' => $residents['resident1@subsync.com']->id],
                ['status' => 'going']
            );
            EventRsvp::firstOrCreate(
                ['event_id' => $createdEvent->id, 'resident_id' => $residents['resident2@subsync.com']->id],
                ['status' => 'going']
            );
            EventRsvp::firstOrCreate(
                ['event_id' => $createdEvent->id, 'resident_id' => $residents['resident4@subsync.com']->id],
                ['status' => 'maybe']
            );
        }

        // ==========================================
        // 13. CONVERSATIONS & MESSAGES (Formal Messaging)
        // ==========================================
        $convs = [
            [
                'title' => 'Pedro Reyes',
                'resident' => $residents['resident1@subsync.com'],
                'messages' => [
                    ['resident_id' => $residents['resident1@subsync.com']->id, 'sender_type' => 'resident', 'content' => 'Good morning Admin, I have submitted my GCash payment proof for June. Could you please confirm validation?', 'time' => now()->subHours(4)],
                    ['sender_type' => 'admin', 'content' => 'Hello Pedro! We received your GCash submission reference GCASH-20260616-9988. Our finance officer is reviewing it this afternoon.', 'time' => now()->subHours(3)],
                    ['resident_id' => $residents['resident1@subsync.com']->id, 'sender_type' => 'resident', 'content' => 'Thank you very much! Have a great day.', 'time' => now()->subHours(2)],
                ],
            ],
            [
                'title' => 'Ana Garcia',
                'resident' => $residents['resident2@subsync.com'],
                'messages' => [
                    ['resident_id' => $residents['resident2@subsync.com']->id, 'sender_type' => 'resident', 'content' => 'Hello Officer Maria, will the road resurfacing on Block 2 affect garage access tomorrow?', 'time' => now()->subDays(1)],
                    ['sender_type' => 'admin', 'content' => 'Hi Ana! Yes, during 9 AM to 12 PM, please park on the designated lot across the park while asphalt cools down.', 'time' => now()->subHours(20)],
                ],
            ],
            [
                'title' => 'Juan dela Cruz (Officer)',
                'officer' => $officer1,
                'messages' => [
                    ['sender_type' => 'admin', 'content' => 'Officer Juan, please inspect the streetlight reported dark near Block 1 Lot 5 during your 8 PM roving patrol tonight.', 'time' => now()->subHours(5)],
                    ['officer_id' => $officer1->id, 'sender_type' => 'officer', 'content' => 'Understood Admin. Will log the inspection on the Issue Tracking tab and coordinate with the maintenance crew.', 'time' => now()->subHours(4)],
                ],
            ],
            [
                'title' => 'Eduardo Cruz',
                'resident' => $residents['resident4@subsync.com'],
                'messages' => [
                    ['resident_id' => $residents['resident4@subsync.com']->id, 'sender_type' => 'resident', 'content' => 'Good afternoon, can we reserve the Multi-Purpose Hall for a private baptism reception next month?', 'time' => now()->subDays(2)],
                    ['sender_type' => 'admin', 'content' => 'Good afternoon Eduardo! Yes, please submit a reservation request with your preferred date. Reservation guidelines are in the HOA Policies tab.', 'time' => now()->subDays(1)],
                ],
            ],
            [
                'title' => 'Katrina Villanueva',
                'resident' => $residents['resident9@subsync.com'],
                'messages' => [
                    ['resident_id' => $residents['resident9@subsync.com']->id, 'sender_type' => 'resident', 'content' => 'Reporting the leaking hydrant valve near our gate in Block 9. Water is still leaking continuously.', 'time' => now()->subHours(6)],
                    ['sender_type' => 'admin', 'content' => 'Hello Katrina, Officer Ricardo and BACIWA technicians have been notified and are on their way with a replacement seal.', 'time' => now()->subHours(5)],
                ],
            ],
        ];

        foreach ($convs as $c) {
            $conversation = Conversation::firstOrCreate(['title' => $c['title']]);

            if (isset($c['resident'])) {
                ConvParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'resident_id' => $c['resident']->id,
                    'participant_type' => 'resident',
                ]);
            } elseif (isset($c['officer'])) {
                ConvParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'officer_id' => $c['officer']->id,
                    'participant_type' => 'officer',
                ]);
            }

            foreach ($c['messages'] as $msg) {
                Message::firstOrCreate(
                    [
                        'conversation_id' => $conversation->id,
                        'content' => $msg['content'],
                    ],
                    [
                        'resident_id' => $msg['resident_id'] ?? null,
                        'officer_id' => $msg['officer_id'] ?? null,
                        'sender_type' => $msg['sender_type'],
                        'created_at' => $msg['time'],
                    ]
                );
            }
        }

        // ==========================================
        // 14. OFFICER FILES & DOCUMENTS
        // ==========================================
        $officerFilesData = [
            [
                'officer_id' => $officer1->id,
                'officer_name' => $officer1->name,
                'original_name' => 'Weekly_Security_Patrol_Report_June_2026.pdf',
                'category' => 'Security',
                'period' => '2026-06',
                'notes' => 'Summary of roving night patrols, gate entry volume, and visitor log compliance for week 1 of June.',
                'file_path' => 'officer-files/weekly_security_patrol_june2026.pdf',
            ],
            [
                'officer_id' => $officer3->id,
                'officer_name' => $officer3->name,
                'original_name' => 'Perimeter_Lighting_Inspection_Audit.pdf',
                'category' => 'Maintenance',
                'period' => '2026-06',
                'notes' => 'Detailed electrical inspection of street lighting poles along Blocks 1 through 12.',
                'file_path' => 'officer-files/perimeter_lighting_audit.pdf',
            ],
            [
                'officer_id' => $officer2->id,
                'officer_name' => $officer2->name,
                'original_name' => 'Community_Assembly_Logistics_Brief.pdf',
                'category' => 'Community',
                'period' => '2026-06',
                'notes' => 'Checklist of sound system, chairs, and attendance verification roster for the June General Assembly.',
                'file_path' => 'officer-files/community_assembly_brief.pdf',
            ],
            [
                'officer_id' => $officer1->id,
                'officer_name' => $officer1->name,
                'original_name' => 'CCTV_System_Health_Check_Report.pdf',
                'category' => 'Security',
                'period' => '2026-05',
                'notes' => 'Status of all 8 DVR channels and gate barrier sensors.',
                'file_path' => 'officer-files/cctv_system_health.pdf',
            ],
        ];

        foreach ($officerFilesData as $f) {
            $pdfContent = $this->placeholderPdf($f['original_name'], $f['category'], $f['officer_name']);
            Storage::disk('local')->put($f['file_path'], $pdfContent);
            Storage::disk('public')->put($f['file_path'], $pdfContent);

            OfficerFile::updateOrCreate(
                ['original_name' => $f['original_name']],
                [
                    'officer_id' => $f['officer_id'],
                    'officer_name' => $f['officer_name'],
                    'original_name' => $f['original_name'],
                    'category' => $f['category'],
                    'period' => $f['period'],
                    'notes' => $f['notes'],
                    'size_bytes' => strlen($pdfContent),
                    'file_path' => $f['file_path'],
                    'archived' => false,
                ]
            );
        }

        // ==========================================
        // 15. SUB-SEEDERS (Report Templates, Expenses, Policies)
        // ==========================================
        $this->call(ReportTemplateSeeder::class);
        $this->call(HoaExpenseSeeder::class);
        $this->call(HoaPolicySeeder::class);
    }

    /** Build a minimal, valid single-page PDF placeholder for officer documents. */
    public function placeholderPdf(string $title, string $category, string $officerName): string
    {
        $lines = [
            ['Terra Nova HOA - Official Officer Report', 18],
            ['', 12],
            ['Document: '.$title, 12],
            ['Category: '.$category, 12],
            ['Submitted By: '.$officerName, 12],
            ['Date Filed: '.now()->format('F d, Y'), 12],
            ['Status: Validated & Archived in Records', 12],
            ['', 12],
            ['This is a system-generated demonstration document for the capstone final defense.', 10],
        ];

        $content = '';
        $y = 720;
        foreach ($lines as $i => [$line, $size]) {
            if ($line !== '') {
                $content .= 'BT /F1 '.$size.' Tf 72 '.$y.' Td ('.$this->pdfEscape($line).") Tj ET\n";
            }
            $y -= $i <= 1 ? 28 : 22;
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            5 => '<< /Length '.strlen($content)." >>\nstream\n".$content.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= 5; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i." 0 obj\n".$objects[$i]."\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xrefPos."\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
