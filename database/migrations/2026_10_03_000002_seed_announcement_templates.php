<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('announcement_templates')->count() > 0) {
            return;
        }

        $now = now();

        DB::table('announcement_templates')->insert([
            [
                'name'             => 'Community Notice',
                'type'             => 'notice',
                'subject_template' => 'Community Notice',
                'body_template'    => "Dear residents,\n\nPlease be informed that [details here].\n\nThank you for your cooperation.\n\nTNHOA Administration",
                'variables'        => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'General Announcement',
                'type'             => 'notice',
                'subject_template' => 'General Announcement',
                'body_template'    => "Attention all residents,\n\n[Announcement details here].\n\nFor questions, please contact the HOA office.\n\nTNHOA Administration",
                'variables'        => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Upcoming Event',
                'type'             => 'event',
                'subject_template' => 'Upcoming Event',
                'body_template'    => "We are pleased to invite all residents to [Event Name].\n\nDate: [Date]\nVenue: [Location]\nTime: [Time]\n\nWe look forward to seeing you there!\n\nTNHOA Administration",
                'variables'        => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);
    }

    public function down(): void
    {
        // Seed data only — nothing to reverse.
    }
};
