<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    /** GET /api/backups — list recent database backups (admin only). */
    public function index()
    {
        if (! session('admin_id')) {
            abort(403);
        }

        $dir = storage_path('app/backups');

        $backups = collect(is_dir($dir) ? File::files($dir) : [])
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'backup-'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->take(30)
            ->map(fn ($f) => [
                'name' => $f->getFilename(),
                'size' => $f->getSize(),
                'date' => date('M d, Y g:i A', $f->getMTime()),
            ])
            ->values();

        return response()->json(['backups' => $backups]);
    }

    /** POST /api/backups/run — run a backup right now (admin only). */
    public function run()
    {
        if (! session('admin_id')) {
            abort(403);
        }

        try {
            Artisan::call('subsync:backup-database');
            $output = trim(Artisan::output());

            return response()->json([
                'success' => str_contains($output, 'Backup created'),
                'message' => $output !== '' ? $output : 'Backup finished.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: '.$e->getMessage(),
            ]);
        }
    }
}
