<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueDateFilterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function issueOn(string $date): IssueReport
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $resident = Resident::create([
            'name' => 'Resident', 'email' => 'r'.uniqid().'@example.test',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);

        $issue = IssueReport::create([
            'resident_id' => $resident->id, 'category' => 'Road / Pavement',
            'title' => 'Pothole '.$date, 'description' => 'x',
            'status' => 'Pending', 'priority' => 'Low',
        ]);
        $issue->forceFill(['created_at' => $date.' 09:00:00'])->save();

        return $issue;
    }

    public function test_a_reversed_date_range_is_swapped_instead_of_returning_nothing(): void
    {
        $admin = $this->admin();
        $this->issueOn('2026-10-10');

        $res = $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/issues?date_from=2026-10-20&date_to=2026-10-01')
            ->assertOk();

        $this->assertCount(1, $res->json(), 'from > to must be swapped, not filter everything out');
    }

    public function test_malformed_dates_are_ignored(): void
    {
        $admin = $this->admin();
        $this->issueOn('2026-10-10');

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/issues?date_from=not-a-date&date_to=2026-10-31')
            ->assertOk()
            ->assertJsonCount(1);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])
            ->getJson('/api/issues/common?date_from=13/13/2026&date_to=%27;DROP')
            ->assertOk();
    }
}
