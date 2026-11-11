<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionEnvTest extends TestCase
{
    public function test_check_command_fails_in_an_unsafe_environment(): void
    {
        // The test environment is intentionally unsafe (debug on, http, array mail…).
        $this->artisan('subsync:check-prod-env')->assertExitCode(1);
    }

    public function test_check_command_passes_with_production_settings(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://subsync.example.com',
            'session.secure' => true,
            'session.encrypt' => true,
            'logging.channels.single.level' => 'warning',
            'mail.default' => 'smtp',
        ]);

        $this->artisan('subsync:check-prod-env')->assertSuccessful();
    }
}
