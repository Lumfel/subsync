<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\IssueReport;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase10Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        return Resident::firstOrCreate(
            ['email' => 'resident@example.test'],
            ['name' => 'Test Resident', 'password' => 'Secret@12345', 'status' => 'Active']
        );
    }

    private function issue(string $category): IssueReport
    {
        return IssueReport::create([
            'resident_id' => $this->resident()->id,
            'category'    => $category,
            'title'       => 'Issue',
            'description' => 'Details.',
            'status'      => 'Pending',
        ]);
    }

    public function test_common_issues_are_ranked_by_frequency(): void
    {
        $this->issue('Other');
        $this->issue('Other');
        $this->issue('Utilities');

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->getJson('/api/issues/common')
            ->assertOk();

        $rows = collect($res->json());
        $this->assertSame('Other', $rows[0]['category']);
        $this->assertSame(2, (int) $rows[0]['total']);
        $this->assertSame(1, (int) $rows[1]['total']);
        $this->assertSame(2, (int) $rows[0]['open']);
    }
}
