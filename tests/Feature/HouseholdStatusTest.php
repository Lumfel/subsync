<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    public function test_household_status_accepts_occupied_and_vacant(): void
    {
        $admin = $this->admin();
        $session = fn () => $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'A']);

        $session()->postJson('/api/households', ['block_lot_number' => 'Block 9 Lot 9', 'status' => 'Occupied'])
            ->assertOk();
        $session()->postJson('/api/households', ['block_lot_number' => 'Block 9 Lot 10', 'status' => 'Vacant'])
            ->assertOk();

        // Default is Occupied when omitted.
        $session()->postJson('/api/households', ['block_lot_number' => 'Block 9 Lot 11'])->assertOk();
        $this->assertDatabaseHas('households', ['block_lot_number' => 'Block 9 Lot 11', 'status' => 'Occupied']);
    }

    public function test_household_status_rejects_old_resident_vocabulary(): void
    {
        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->postJson('/api/households', ['block_lot_number' => 'Block 9 Lot 12', 'status' => 'Active'])
            ->assertStatus(422);
    }
}
