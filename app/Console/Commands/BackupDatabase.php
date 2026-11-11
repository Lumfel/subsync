<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Creates a dated database backup in storage/app/backups and prunes old ones.
 *
 * MySQL : mysqldump to `backup-YYYYmmdd-HHMMSS.sql`
 * SQLite: file copy to `backup-YYYYmmdd-HHMMSS.sqlite`
 *
 * The mysqldump binary is found via MYSQLDUMP_PATH in .env, the common
 * Laragon location, or PATH. Scheduled daily at 02:00 (routes/console.php).
 */
class BackupDatabase extends Command
{
    protected $signature = 'subsync:backup-database {--keep=14 : Number of backups to keep}';

    protected $description = 'Back up the application database to storage/app/backups (and prune old backups)';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Ymd-His');
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $result = $this->backupSqlite($dir, $stamp);
        } else {
            $result = $this->backupMysql($dir, $stamp);
        }

        if ($result !== true) {
            $this->warn($result === null ? 'Nothing to back up.' : $result);

            return self::SUCCESS; // graceful: never fail the scheduler/request
        }

        $target = $dir.'/'.($connection === 'sqlite' ? "backup-{$stamp}.sqlite" : "backup-{$stamp}.sql");
        $this->prune($dir, (int) $this->option('keep'));
        $this->info('Backup created: '.basename($target).' ('.number_format(filesize($target) / 1024, 1).' KB)');

        return self::SUCCESS;
    }

    /** @return true|string|null true on success, message on skip/failure */
    private function backupSqlite(string $dir, string $stamp): true|string|null
    {
        $source = config('database.connections.sqlite.database');
        if (! $source || $source === ':memory:' || ! is_file($source)) {
            return null;
        }

        $target = "{$dir}/backup-{$stamp}.sqlite";

        // VACUUM INTO gives a consistent snapshot; the app runs SQLite in WAL mode,
        // so a plain file copy can miss committed WAL frames (torn/stale backup).
        try {
            DB::statement("VACUUM INTO '".str_replace("'", "''", $target)."'");

            return true;
        } catch (\Throwable $e) {
            // Very old SQLite without VACUUM INTO — fall back to a plain copy.
            File::copy($source, $target);

            return true;
        }
    }

    /** @return true|string|null true on success, message on skip/failure */
    private function backupMysql(string $dir, string $stamp): true|string|null
    {
        $cfg = config('database.connections.'.config('database.default'));
        $dump = $this->findMysqldump();

        if (! $dump) {
            return 'mysqldump not found — set MYSQLDUMP_PATH in .env or add mysqldump to PATH.';
        }

        $target = "{$dir}/backup-{$stamp}.sql";

        $args = [
            $dump,
            '--host='.$cfg['host'],
            '--port='.$cfg['port'],
            '--user='.$cfg['username'],
            '--single-transaction',
            '--routines',
            '--skip-lock-tables',
            '--databases',
            $cfg['database'],
            '--result-file='.$target,
        ];

        if (! empty($cfg['password'])) {
            array_splice($args, 3, 0, ['--password='.$cfg['password']]);
        }

        $process = new Process($args, null, null, null, 300);

        try {
            $process->run();
        } catch (\Throwable $e) {
            // Timed out (ProcessTimedOutException) or failed to start: never keep
            // the half-written dump, which would otherwise be pruned in as "valid".
            if (is_file($target)) {
                File::delete($target);
            }

            return 'mysqldump failed: '.$e->getMessage();
        }

        if (! $process->isSuccessful()) {
            if (is_file($target)) {
                File::delete($target);
            }

            return 'mysqldump failed: '.trim($process->getErrorOutput() ?: $process->getOutput());
        }

        if (! is_file($target) || filesize($target) === 0) {
            return 'Backup file is empty — aborting.';
        }

        return true;
    }

    private function findMysqldump(): ?string
    {
        $candidates = array_filter([
            env('MYSQLDUMP_PATH'),
            'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe',
        ]);
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        // Any other Laragon-installed MySQL version.
        foreach (glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe') ?: [] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        // Fall back to PATH.
        $probe = Process::fromShellCommandline('mysqldump --version');
        $probe->run();

        return $probe->isSuccessful() ? 'mysqldump' : null;
    }

    private function prune(string $dir, int $keep): void
    {
        $keep = max(1, $keep);

        $files = collect(File::files($dir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'backup-'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        foreach ($files->slice($keep) as $old) {
            File::delete($old->getPathname());
        }
    }
}
