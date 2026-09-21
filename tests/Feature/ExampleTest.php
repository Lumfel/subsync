<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('SubSync')
            ->assertSee('Issue reporting')
            ->assertSee('Subdivision mapping')
            ->assertSee(route('login'))
            ->assertDontSee('Administrator access'); // admin login intentionally hidden from public landing
    }

    public function test_mapping_page_loads_with_leaflet_map(): void
    {
        $response = $this->get('/mapping');

        $response->assertStatus(200)
            ->assertSee('leaflet')
            ->assertSee('L.map')
            ->assertSee('Terra Nova');
    }
}
