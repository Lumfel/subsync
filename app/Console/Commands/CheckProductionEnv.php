<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Verifies that the current environment is production-safe before a deployment.
 *
 * Covers the OWASP A05 / ASVS items and the RA 10173 logging expectations:
 * debug off, HTTPS app URL, secure + encrypted sessions, real mail transport,
 * and a non-debug log level.
 *
 * Usage: php artisan subsync:check-prod-env   (exit code 1 when anything fails)
 */
class CheckProductionEnv extends Command
{
    protected $signature = 'subsync:check-prod-env';

    protected $description = 'Verify production-safe settings (debug off, HTTPS, secure/encrypted sessions, real mail, log level)';

    public function handle(): int
    {
        $logLevel = strtolower((string) (config('logging.channels.single.level')
            ?: config('logging.channels.stack.level')
            ?: 'debug'));
        $mailer = (string) config('mail.default');

        $checks = [
            ['APP_ENV', config('app.env') === 'production', (string) config('app.env'), 'production'],
            ['APP_DEBUG', config('app.debug') === false, config('app.debug') ? 'true' : 'false', 'false'],
            ['APP_KEY', config('app.key') !== '' && config('app.key') !== null, config('app.key') ? 'set' : 'missing', 'set'],
            ['APP_URL', str_starts_with((string) config('app.url'), 'https://'), (string) config('app.url'), 'https://…'],
            ['SESSION_SECURE_COOKIE', config('session.secure') === true, config('session.secure') ? 'true' : 'false', 'true'],
            ['SESSION_ENCRYPT', config('session.encrypt') === true, config('session.encrypt') ? 'true' : 'false', 'true'],
            ['LOG_LEVEL', $logLevel !== 'debug', $logLevel, 'warning / error'],
            ['MAIL_MAILER', ! in_array($mailer, ['log', 'array'], true), $mailer, 'smtp / mailgun / …'],
        ];

        $failures = 0;
        $rows = [];

        foreach ($checks as [$name, $ok, $actual, $expected]) {
            if (! $ok) {
                $failures++;
            }
            $rows[] = [$name, $ok ? 'OK' : 'FAIL', $actual, $expected];
        }

        $this->table(['Setting', 'Result', 'Current', 'Expected'], $rows);

        if ($failures > 0) {
            $this->error("{$failures} production setting(s) need attention before deploying.");

            return self::FAILURE;
        }

        $this->info('All production environment settings look safe.');

        return self::SUCCESS;
    }
}
