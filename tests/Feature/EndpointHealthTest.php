<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Broad health sweep: hits every API endpoint as each role and asserts nothing
 * returns a 5xx. Catches regressions (bad route params, undefined keys, typos)
 * without pinning exact status codes, so it stays green as behavior evolves.
 */
class EndpointHealthTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Officer $officer;

    private Resident $resident;

    private array $failures = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['name' => 'Admin', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
        $this->officer = Officer::create(['name' => 'Officer', 'email' => 'o@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
        $h = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $this->resident = Resident::create(['name' => 'Resident', 'email' => 'r@x.com', 'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $h->id]);
    }

    private function hit(string $method, string $uri, array $payload = []): void
    {
        $method = strtoupper($method);
        $resp = match ($method) {
            'GET' => $this->getJson($uri),
            'POST' => $this->postJson($uri, $payload),
            'PUT' => $this->putJson($uri, $payload),
            'PATCH' => $this->patchJson($uri, $payload),
            'DELETE' => $this->deleteJson($uri, $payload),
        };

        if ($resp->getStatusCode() >= 500) {
            $this->failures[] = sprintf('%s %s -> %d %s', $method, $uri, $resp->getStatusCode(), $resp->exception ? get_class($resp->exception) : '');
        }
    }

    private function assertHealthy(): void
    {
        $this->assertSame([], $this->failures, "Endpoints returned 5xx:\n".implode("\n", $this->failures));
    }

    private const GETS = [
        '/api/dashboard-data', '/api/residents', '/api/households', '/api/officers', '/api/families',
        '/api/announcements', '/api/announcement-templates', '/api/report-templates', '/api/issues',
        '/api/issues/urgent-check', '/api/issues/common', '/api/recommendations', '/api/officer-files',
        '/api/payments', '/api/ledger/aging', '/api/assessments', '/api/budgets', '/api/export/residents',
        '/api/export/issues', '/api/export/payments', '/api/export/hoa-expenses', '/api/map/all',
        '/api/map/settings', '/api/map/households', '/api/map/facilities', '/api/map/issues', '/api/alerts',
        '/api/search?q=a', '/api/hoa-expenses', '/api/hoa-policies', '/api/gcash-settings', '/api/settings',
        '/api/resident/portal-data', '/api/residents/me', '/api/household-members', '/api/reports/my',
        '/api/issues/my', '/api/recommendations/my', '/api/payments/my', '/api/ledger/my',
        '/api/transparency/summary', '/api/stats', '/api/messages/threads', '/api/messages/officers',
    ];

    public function test_admin_matrix_has_no_server_errors(): void
    {
        $this->withSession(['admin_id' => $this->admin->id, 'admin_name' => 'Admin']);
        foreach (self::GETS as $u) {
            $this->hit('GET', $u);
        }
        $this->hit('POST', '/api/announcements', ['title' => 'T', 'content' => 'C']);
        $this->hit('POST', '/api/announcement-templates', ['name' => 'N', 'type' => 'T', 'subject_template' => 'S', 'body_template' => 'B']);
        $this->hit('POST', '/api/report-templates', ['name' => 'N', 'type' => 'T', 'subject_template' => 'S', 'body_template' => 'B']);
        $this->hit('POST', '/api/families', ['family_name' => 'Fam']);
        $this->hit('POST', '/api/officers', ['name' => 'N', 'email' => 'n@x.com', 'password' => 'Secret@12345']);
        $this->hit('POST', '/api/hoa-expenses', ['title' => 'T', 'amount' => 100, 'expense_date' => now()->toDateString(), 'category' => 'Utilities']);
        $this->hit('POST', '/api/hoa-policies', ['title' => 'T', 'description' => 'D', 'category' => 'C', 'effective_date' => now()->toDateString()]);
        $this->hit('POST', '/api/gcash-settings', ['remove_qr' => 1]);
        $this->hit('PUT', '/api/map/settings', ['min_lat' => 1, 'max_lat' => 2, 'min_lng' => 1, 'max_lng' => 2]);
        $this->hit('POST', '/api/map/facility', ['name' => 'F', 'latitude' => 1.5, 'longitude' => 1.5]);
        $this->assertHealthy();
    }

    public function test_officer_matrix_has_no_server_errors(): void
    {
        $this->actingAs($this->officer, 'officer');
        foreach (self::GETS as $u) {
            $this->hit('GET', $u);
        }
        $this->hit('PUT', '/api/map/settings', ['min_lat' => 1, 'max_lat' => 2, 'min_lng' => 1, 'max_lng' => 2]);
        $this->hit('POST', '/api/map/facility', ['name' => 'F', 'latitude' => 1.5, 'longitude' => 1.5]);
        $this->hit('POST', '/api/officers', ['name' => 'N', 'email' => 'n2@x.com', 'password' => 'Secret@12345']);
        $this->assertHealthy();
    }

    public function test_resident_matrix_has_no_server_errors(): void
    {
        $this->actingAs($this->resident, 'resident');
        foreach (self::GETS as $u) {
            $this->hit('GET', $u);
        }
        $this->hit('GET', '/api/announcements?status=Archived');
        $this->hit('POST', '/api/issues', ['category' => 'Noise', 'title' => 'T', 'description' => 'D']);
        $this->hit('POST', '/api/recommendations', ['title' => 'T', 'description' => 'D']);
        $this->hit('PUT', "/api/residents/{$this->resident->id}", ['name' => 'R2']);
        $this->assertHealthy();
    }

    public function test_guest_and_badids_have_no_server_errors(): void
    {
        foreach (['/api/stats', '/api/dashboard-data', '/api/residents', '/api/officers', '/api/settings'] as $u) {
            $this->hit('GET', $u);
        }

        $this->withSession(['admin_id' => $this->admin->id, 'admin_name' => 'Admin']);
        foreach ([
            'GET' => ['/api/ledger/abc', '/api/residents/abc/statement', '/api/issues/abc/history', '/api/budgets/abc', '/api/assessments/abc'],
            'PUT' => ['/api/officers/abc', '/api/hoa-expenses/abc', '/api/families/abc'],
            'DELETE' => ['/api/officers/abc', '/api/officer-files/abc'],
        ] as $method => $uris) {
            foreach ($uris as $u) {
                $this->hit($method, $u);
            }
        }
        $this->assertHealthy();
    }
}
