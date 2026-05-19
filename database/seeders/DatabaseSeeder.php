<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Officer;
use App\Models\Household;
use App\Models\Resident;
use App\Models\HouseholdMember;
use App\Models\Announcement;
use App\Models\IssueReport;
use App\Models\Recommendation;
use App\Models\FinancialRecord;
use App\Models\Delinquent;
use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Message;
use App\Models\Facility;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@subsync.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@12345'),
                'status'   => 'Active',
            ]
        );

        // Officers
        $officer1 = Officer::firstOrCreate(
            ['email' => 'officer1@subsync.com'],
            [
                'name'             => 'Juan dela Cruz',
                'password'         => Hash::make('Officer@123'),
                'contact_number'   => '09171234567',
                'status'           => 'Active',
                'role_description' => 'Security Officer',
            ]
        );

        $officer2 = Officer::firstOrCreate(
            ['email' => 'officer2@subsync.com'],
            [
                'name'             => 'Maria Santos',
                'password'         => Hash::make('Officer@123'),
                'contact_number'   => '09271234567',
                'status'           => 'Active',
                'role_description' => 'Community Liaison',
            ]
        );

        // Households
        $hh1 = Household::firstOrCreate(
            ['block_lot_number' => 'Blk 1 Lot 5'],
            ['status' => 'Active', 'latitude' => 10.6230, 'longitude' => 122.9610]
        );
        $hh2 = Household::firstOrCreate(
            ['block_lot_number' => 'Blk 2 Lot 3'],
            ['status' => 'Active', 'latitude' => 10.6235, 'longitude' => 122.9620]
        );
        $hh3 = Household::firstOrCreate(
            ['block_lot_number' => 'Blk 3 Lot 8'],
            ['status' => 'Delinquent', 'latitude' => 10.6220, 'longitude' => 122.9605]
        );
        $hh4 = Household::firstOrCreate(
            ['block_lot_number' => 'Blk 4 Lot 2'],
            ['status' => 'Active', 'latitude' => 10.6240, 'longitude' => 122.9615]
        );
        $hh5 = Household::firstOrCreate(
            ['block_lot_number' => 'Blk 5 Lot 10'],
            ['status' => 'Active', 'latitude' => 10.6225, 'longitude' => 122.9630]
        );

        // Residents
        $resident1 = Resident::firstOrCreate(
            ['email' => 'resident1@subsync.com'],
            [
                'house_id'        => $hh1->id,
                'name'            => 'Pedro Reyes',
                'password'        => Hash::make('Resident@123'),
                'contact_number'  => '09181234567',
                'status'          => 'Active',
                'current_balance' => 500.00,
            ]
        );
        $resident2 = Resident::firstOrCreate(
            ['email' => 'resident2@subsync.com'],
            [
                'house_id'        => $hh2->id,
                'name'            => 'Ana Garcia',
                'password'        => Hash::make('Resident@123'),
                'contact_number'  => '09281234567',
                'status'          => 'Active',
                'current_balance' => 0.00,
            ]
        );
        $resident3 = Resident::firstOrCreate(
            ['email' => 'resident3@subsync.com'],
            [
                'house_id'        => $hh3->id,
                'name'            => 'Carlos Bautista',
                'password'        => Hash::make('Resident@123'),
                'contact_number'  => '09381234567',
                'status'          => 'Delinquent',
                'current_balance' => 2500.00,
            ]
        );
        $resident4 = Resident::firstOrCreate(
            ['email' => 'resident4@subsync.com'],
            [
                'house_id'        => $hh4->id,
                'name'            => 'Test Resident',
                'password'        => Hash::make('Resident@123'),
                'contact_number'  => '09481234567',
                'status'          => 'Active',
                'current_balance' => 0.00,
            ]
        );

        // Household Members
        if (!\App\Models\HouseholdMember::where('house_id', $hh1->id)->exists()) {
            HouseholdMember::create(['house_id' => $hh1->id, 'name' => 'Lina Reyes', 'relationship' => 'Spouse', 'contact_number' => '09111111111']);
            HouseholdMember::create(['house_id' => $hh1->id, 'name' => 'Ricky Reyes', 'relationship' => 'Child', 'contact_number' => null]);
        }
        if (!\App\Models\HouseholdMember::where('house_id', $hh2->id)->exists()) {
            HouseholdMember::create(['house_id' => $hh2->id, 'name' => 'Bert Garcia', 'relationship' => 'Spouse', 'contact_number' => '09222222222']);
        }

        // Announcements
        if (!Announcement::count()) {
            Announcement::create(['admin_id' => $admin->id, 'officer_id' => null, 'title' => 'Monthly Dues Reminder', 'content' => 'Kindly settle your monthly dues before May 31, 2026.', 'tag' => 'Finance']);
            Announcement::create(['admin_id' => $admin->id, 'officer_id' => null, 'title' => 'Road Repair Schedule', 'content' => 'Main road repair scheduled for June 5–7, 2026. Expect traffic.', 'tag' => 'Maintenance']);
            Announcement::create(['admin_id' => null, 'officer_id' => $officer1->id, 'title' => 'Barangay Assembly', 'content' => 'Barangay assembly on June 10 at 8AM. Attendance required.', 'tag' => 'Community']);
            Announcement::create(['admin_id' => null, 'officer_id' => $officer1->id, 'title' => 'Community Meeting - Block 1', 'content' => 'All Block 1 residents are invited to the community meeting on June 15 at 7PM.', 'tag' => 'notice']);
            Announcement::create(['admin_id' => $admin->id, 'officer_id' => null, 'title' => 'Test System Announcement', 'content' => 'This is a test announcement from the admin dashboard.', 'tag' => 'notice']);
        }

        // Issue Reports
        if (!IssueReport::count()) {
            IssueReport::create(['resident_id' => $resident1->id, 'category' => 'Infrastructure', 'title' => 'Broken Street Light', 'description' => 'Street light near Blk 1 Lot 5 has been out for 3 days.', 'status' => 'In Progress', 'latitude' => 10.6231, 'longitude' => 122.9611]);
            IssueReport::create(['resident_id' => $resident2->id, 'category' => 'Noise', 'title' => 'Loud Music at Night', 'description' => 'Neighbor plays loud music past midnight.', 'status' => 'Resolved', 'latitude' => 10.6236, 'longitude' => 122.9621]);
            IssueReport::create(['resident_id' => $resident3->id, 'category' => 'Drainage', 'title' => 'Clogged Drain', 'description' => 'Drainage near Blk 3 is clogged causing flooding.', 'status' => 'Resolved', 'latitude' => 10.6221, 'longitude' => 122.9606]);
        }

        // Recommendations
        if (!Recommendation::count()) {
            Recommendation::create(['resident_id' => $resident1->id, 'title' => 'Install More Street Lights', 'description' => 'Please install additional street lights along the main road.', 'status' => 'Pending']);
            Recommendation::create(['resident_id' => $resident2->id, 'title' => 'Community Garden', 'description' => 'Suggest creating a community garden in the empty lot near Blk 2.', 'status' => 'Approved']);
        }

        // Financial Records
        if (!FinancialRecord::count()) {
            FinancialRecord::create(['resident_id' => $resident1->id, 'admin_id' => $admin->id, 'record_type' => 'Due', 'amount' => 500.00, 'description' => 'Monthly dues May 2026', 'record_date' => '2026-05-31']);
            FinancialRecord::create(['resident_id' => $resident2->id, 'admin_id' => $admin->id, 'record_type' => 'Payment', 'amount' => 500.00, 'description' => 'Monthly dues May 2026 - Paid', 'record_date' => '2026-05-10']);
            FinancialRecord::create(['resident_id' => $resident3->id, 'admin_id' => $admin->id, 'record_type' => 'Due', 'amount' => 2500.00, 'description' => 'Outstanding balance', 'record_date' => '2026-05-31']);
        }

        // Delinquents
        if (!Delinquent::count()) {
            Delinquent::create(['house_id' => $hh3->id, 'reason' => 'Unpaid dues for 5 months', 'date_flagged' => '2026-05-01']);
        }

        // Conversations
        if (!Conversation::count()) {
            $conv1 = Conversation::create(['title' => 'Pedro Reyes']);
            ConvParticipant::create(['conversation_id' => $conv1->id, 'resident_id' => $resident1->id, 'participant_type' => 'resident']);
            Message::create(['conversation_id' => $conv1->id, 'resident_id' => $resident1->id, 'sender_type' => 'resident', 'content' => 'Hello Admin, I have a question about my dues.', 'created_at' => now()->subHours(2)]);
            Message::create(['conversation_id' => $conv1->id, 'sender_type' => 'admin', 'content' => 'Hello Pedro! How can I help you?', 'created_at' => now()->subHour()]);

            $conv2 = Conversation::create(['title' => 'Ana Garcia']);
            ConvParticipant::create(['conversation_id' => $conv2->id, 'resident_id' => $resident2->id, 'participant_type' => 'resident']);
            Message::create(['conversation_id' => $conv2->id, 'resident_id' => $resident2->id, 'sender_type' => 'resident', 'content' => 'Good morning! Just checking on the road repair schedule.', 'created_at' => now()->subDay()]);

            $conv3 = Conversation::create(['title' => 'Juan dela Cruz (Officer)']);
            ConvParticipant::create(['conversation_id' => $conv3->id, 'officer_id' => $officer1->id, 'participant_type' => 'officer']);
            Message::create(['conversation_id' => $conv3->id, 'sender_type' => 'admin', 'content' => 'Good morning, Officer! Please handle the noise complaint in Block 2.', 'created_at' => now()->subHours(3)]);
            Message::create(['conversation_id' => $conv3->id, 'officer_id' => $officer1->id, 'sender_type' => 'officer', 'content' => 'Understood, I will check it out now.', 'created_at' => now()->subHours(2)]);
        }
        // Facilities (for Maps section)
        if (!Facility::count()) {
            Facility::create(['name' => 'Clubhouse', 'description' => 'Main clubhouse for events and gatherings.', 'latitude' => 10.6228, 'longitude' => 122.9614]);
            Facility::create(['name' => 'Basketball Court', 'description' => 'Outdoor basketball court open daily 6AM–10PM.', 'latitude' => 10.6232, 'longitude' => 122.9608]);
            Facility::create(['name' => 'Main Entrance Gate', 'description' => 'Primary entrance and exit of the subdivision.', 'latitude' => 10.6222, 'longitude' => 122.9625]);
            Facility::create(['name' => 'Children\'s Playground', 'description' => 'Playground area for children aged 3–12.', 'latitude' => 10.6238, 'longitude' => 122.9602]);
            Facility::create(['name' => 'Multi-Purpose Hall', 'description' => 'Available for rent for community events.', 'latitude' => 10.6224, 'longitude' => 122.9619]);
        }
    }
}

