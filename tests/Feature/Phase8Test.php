<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class Phase8Test extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(array $overrides = []): SocialiteUser
    {
        return (new SocialiteUser)->map(array_merge([
            'id' => 'g-123',
            'nickname' => null,
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@gmail.com',
            'avatar' => 'https://example.com/a.png',
        ], $overrides));
    }

    private function resident(array $attrs = []): Resident
    {
        return Resident::create(array_merge([
            'name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com',
            'password' => 'Secret@12345', 'status' => 'Active',
        ], $attrs));
    }

    public function test_new_google_resident_is_created_pending_and_asked_for_block_lot(): void
    {
        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->get('/auth/google/callback')->assertRedirect(url('auth/google/complete'));

        $this->assertDatabaseHas('residents', [
            'email' => 'juan@gmail.com',
            'status' => 'Pending',
            'google_id' => 'g-123',
            'avatar' => 'https://example.com/a.png',
        ]);
    }

    public function test_completing_block_lot_creates_household_and_keeps_pending(): void
    {
        $resident = $this->resident(['status' => 'Pending']);

        $this->withSession(['google_pending_resident_id' => $resident->id])
            ->post('/auth/google/complete', ['block_lot' => 'Block 3 Lot 12', 'contact_number' => '09171234567'])
            ->assertRedirect(url('login'));

        $this->assertDatabaseHas('households', ['block_lot_number' => 'Block 3 Lot 12']);

        $resident->refresh();
        $this->assertNotNull($resident->house_id);
        $this->assertSame('Pending', $resident->status);
        $this->assertSame('09171234567', $resident->contact_number);
    }

    public function test_active_resident_with_household_logs_in_via_google(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $resident = $this->resident(['status' => 'Active', 'house_id' => $house->id]);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->get('/auth/google/callback')->assertRedirect(url('residents'));

        $this->assertAuthenticatedAs($resident, 'resident');
        $this->assertSame('g-123', $resident->fresh()->google_id);
    }

    public function test_active_resident_without_household_is_asked_for_block_lot(): void
    {
        $resident = $this->resident(['status' => 'Active']);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->get('/auth/google/callback')->assertRedirect(url('auth/google/complete'));
        $this->assertGuest('resident');
        $this->assertNull($resident->fresh()->house_id);
    }

    public function test_pending_resident_with_household_cannot_login_via_google(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 2 Lot 2']);
        $this->resident(['status' => 'Pending', 'house_id' => $house->id]);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->get('/auth/google/callback')->assertRedirect(url('login'));
        $this->assertGuest('resident');
    }

    public function test_complete_page_requires_pending_session(): void
    {
        $this->get('/auth/google/complete')->assertRedirect(url('login'));
    }

    public function test_active_resident_completing_block_lot_logs_in(): void
    {
        $resident = $this->resident(['status' => 'Active']);

        $this->withSession(['google_pending_resident_id' => $resident->id])
            ->get('/auth/google/complete')->assertOk();

        $this->withSession(['google_pending_resident_id' => $resident->id])
            ->post('/auth/google/complete', ['block_lot' => 'Block 5 Lot 5'])
            ->assertRedirect(url('residents'));

        $this->assertAuthenticatedAs($resident->fresh(), 'resident');
    }

    public function test_google_login_persists_to_residents_portal(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 9 Lot 9']);
        $resident = $this->resident(['status' => 'Active', 'house_id' => $house->id]);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->get('/auth/google/callback')->assertRedirect(url('residents'));
        $this->get('/residents')->assertOk();
    }

    public function test_officer_google_signup_creates_pending_officer(): void
    {
        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser([
            'name' => 'Off One', 'email' => 'off@gmail.com',
        ]));

        $this->withSession(['google_role' => 'officer'])
            ->get('/auth/google/callback')
            ->assertRedirect(url('login'));

        $this->assertGuest('officer');
        $this->assertDatabaseHas('officers', [
            'email' => 'off@gmail.com',
            'status' => 'Pending',
            'google_id' => 'g-123',
            'avatar' => 'https://example.com/a.png',
        ]);
    }

    public function test_existing_active_officer_logs_in_via_google(): void
    {
        $officer = Officer::create([
            'name' => 'Off One', 'email' => 'juan@gmail.com',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->withSession(['google_role' => 'officer'])
            ->get('/auth/google/callback')
            ->assertRedirect(url('officer-portal'));

        $this->assertAuthenticatedAs($officer, 'officer');
        $this->assertSame('g-123', $officer->fresh()->google_id);
    }

    public function test_pending_officer_cannot_login_via_google(): void
    {
        Officer::create([
            'name' => 'Off One', 'email' => 'juan@gmail.com',
            'password' => 'Secret@12345', 'status' => 'Pending',
        ]);

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser());

        $this->withSession(['google_role' => 'officer'])
            ->get('/auth/google/callback')
            ->assertRedirect(url('login'));

        $this->assertGuest('officer');
    }

    public function test_google_redirect_returns_to_the_current_host(): void
    {
        $response = $this->get('/auth/google?role=resident');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $location);
        $this->assertStringContainsString(
            'redirect_uri='.rawurlencode(url('auth/google/callback')),
            $location
        );
    }

    public function test_failed_google_exchange_redirects_with_a_friendly_error(): void
    {
        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);

        $this->get('/auth/google/callback')
            ->assertRedirect(url('login'))
            ->assertSessionHas('error', 'Google sign-in failed. Please try again.');
    }

    public function test_portal_hint_cookie_survives_unencrypted_for_google_oauth(): void
    {
        // ResolvePortalSession runs BEFORE EncryptCookies, so an encrypted hint
        // cookie would never match and every callback would fall back to the
        // resident session (which broke the officer Google login).
        $res = $this->get('/auth/google?role=officer')->assertRedirect();

        $hint = collect($res->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'subsync_portal_hint');

        $this->assertNotNull($hint, 'the OAuth portal hint cookie must be queued');
        $this->assertSame('officer', $hint->getValue(), 'the hint must stay unencrypted');
    }

    public function test_officer_google_callback_finds_its_own_session_state(): void
    {
        $state = 'state-for-officer-flow';

        Socialite::shouldReceive('driver->user')->andReturn($this->fakeGoogleUser([
            'name' => 'Off One', 'email' => 'off@gmail.com',
        ]));

        // The callback arrives from Google with the hint cookie + matching state.
        $this->withUnencryptedCookie('subsync_portal_hint', 'officer')
            ->withSession(['google_role' => 'officer', 'state' => $state])
            ->get('/auth/google/callback?state='.$state.'&code=test-code')
            ->assertRedirect(url('login')); // new officer → pending → login

        $this->assertDatabaseHas('officers', [
            'email' => 'off@gmail.com',
            'status' => 'Pending',
        ]);
    }
}
