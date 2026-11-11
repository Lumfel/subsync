<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementTemplateController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpenseRequestController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\GcashSettingController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HoaExpenseController;
use App\Http\Controllers\HoaPolicyController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StreamController;
use App\Models\Household;
use App\Support\Actor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public landing page
Route::get('/', function () {
    return view('landing');
});

// Public data-privacy notice (RA 10173)
Route::view('/privacy', 'privacy')->name('privacy');

// Resident / Officer login
Route::get('/login', function () {
    return view('layouts.login_residen_officers');
})->name('login')->middleware('cache.headers:no_store;private');

Route::get('/register', function () {
    return view('layouts.login_residen_officers', ['initialTab' => 'register']);
})->name('register')->middleware('cache.headers:no_store;private');

Route::post('/login', [AuthController::class, 'residentLogin'])
    ->name('login.post')
    ->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])
    ->name('register.post')
    ->middleware('throttle:10,1');

// Google OAuth login (residents and officers; admin stays password-only)
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/complete', [GoogleAuthController::class, 'showComplete'])->name('google.complete');
Route::post('/auth/google/complete', [GoogleAuthController::class, 'complete'])->name('google.complete.post');

// Admin login — kept off the public /login page and on a non-obvious path.
Route::get('/admin-access', function () {
    return view('layouts.login_admin')->with('noindex', true);
})->name('admin.login')->middleware('cache.headers:no_store;private');

Route::post('/admin-access', [AuthController::class, 'adminLogin'])
    ->name('admin.login.post')
    ->middleware('throttle:5,1'); // max 5 attempts per minute

Route::get('/mapping', function () {
    return view('layouts.mapping');
})->name('mapping');

// Protected views
Route::get('/dashboard', function () {
    return view('layouts.admin-dashboard', [
        'adminName' => session('admin_name', 'Admin'),
        'adminId' => session('admin_id'),
        'prefs' => Actor::prefs(),
        'role' => 'admin',
    ]);
})->name('dashboard')->middleware(['admin.auth', 'cache.headers:no_store;private']);

Route::get('/officer-portal', function () {
    $officer = Auth::guard('officer')->user();

    return view('layouts.officer-portal', [
        'officerName' => $officer?->name ?? 'Officer',
        'officerId' => $officer?->id,
        'avatar' => $officer?->avatar,
        'prefs' => Actor::prefs(),
        'role' => 'officer',
    ]);
})->name('officer.portal')->middleware(['officer.auth', 'cache.headers:no_store;private']);

Route::get('/residents', function () {
    $resident = Auth::guard('resident')->user();
    request()->session()->forget(['admin_id', 'admin_name']);
    request()->session()->put('active_role', 'resident');
    request()->session()->put('resident_id', $resident?->id);
    $household = $resident?->house_id
        ? Household::query()->find($resident->house_id)
        : null;

    return view('layouts.residents', [
        'residentName' => $resident?->name ?? 'Resident',
        'residentId' => $resident?->id,
        'houseId' => $resident?->house_id,
        'blockLot' => $household?->block_lot_number ?? '',
        'avatar' => $resident?->avatar,
        'balance' => (float) ($resident?->current_balance ?? 0),
        'prefs' => Actor::prefs(),
        'role' => 'resident',
    ]);
})->name('residents')->middleware(['auth:resident', 'cache.headers:no_store;private']);

// Printable issued documents (staff only)
Route::get('/reports/{id}/print', [ReportController::class, 'printView'])
    ->name('reports.print')
    ->middleware(['portal.role:admin,officer', 'cache.headers:no_store;private']);

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Standalone settings page (profile, security, appearance, notifications) — all roles
Route::get('/settings', [SettingsController::class, 'page'])
    ->name('settings')
    ->middleware(['portal.role:admin,officer,resident', 'cache.headers:no_store;private']);

// Design system reference — canonical tokens + Tailwind/daisyUI kit (admin only).
// Same guard as /dashboard: guests are redirected to the admin login, not 403'd.
Route::get('/design-system', function () {
    return view('layouts.design-system');
})->name('design-system')->middleware(['admin.auth', 'cache.headers:no_store;private']);

// ═══ API ROUTES ═══

// Combined dashboard data (single request replaces 11+ parallel calls)
Route::middleware('portal.role:admin,officer')->group(function () {
    Route::get('/api/dashboard-data', [DashboardController::class, 'data']);
});

// Server-Sent Events — instant push for announcements & issues (admin + officer + resident)
Route::middleware('portal.role:admin,officer,resident')->group(function () {
    Route::get('/api/stream/updates', [StreamController::class, 'updates']);
});

// System tools (admin only) — database backups + activity trail
Route::middleware('portal.role:admin')->group(function () {
    Route::get('/api/backups', [BackupController::class, 'index']);
    Route::post('/api/backups/run', [BackupController::class, 'run']);
    Route::get('/api/activity-logs', [ActivityLogController::class, 'index']);
});

// Map API
Route::middleware('portal.role:admin,officer,resident')->group(function () {
    Route::prefix('api/map')->group(function () {
        Route::get('/settings', [MapController::class, 'settings']);
        Route::get('/all', [MapController::class, 'all']);
        Route::get('/households', [MapController::class, 'households']);
        Route::get('/facilities', [MapController::class, 'facilities']);
        Route::get('/issues', [MapController::class, 'issues']);
    });
});
Route::middleware('portal.role:admin')->group(function () {
    Route::prefix('api/map')->group(function () {
        Route::put('/settings', [MapController::class, 'updateSettings']);
        Route::post('/facility', [MapController::class, 'storeFacility']);
        Route::put('/facility/{id}', [MapController::class, 'updateFacility']);
        Route::delete('/facility/{id}', [MapController::class, 'deleteFacility']);
        Route::put('/household/{id}/location', [MapController::class, 'updateHouseholdLocation']);
    });
});

// Residents & Households API
Route::prefix('api')->group(function () {
    Route::get('/alerts', [AlertController::class, 'index'])->middleware('portal.role:admin,officer,resident');
    // Stats
    Route::get('/stats', [ResidentController::class, 'stats'])->middleware('portal.role:admin,officer,resident');

    // User settings (profile / password / photo / preferences) + global search — all roles
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/settings', [SettingsController::class, 'show']);
        Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
        Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->middleware('throttle:5,1');
        Route::post('/settings/photo', [SettingsController::class, 'updatePhoto']);
        Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences']);
        Route::get('/settings/export', [SettingsController::class, 'exportData']);
        Route::post('/settings/erasure-request', [SettingsController::class, 'requestErasure'])->middleware('throttle:5,1');
        Route::get('/search', [SearchController::class, 'index']);
    });

    // Combined portal data (single request replaces 6+ parallel calls)
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/resident/portal-data', [ResidentController::class, 'portalData']);
        Route::get('/residents/me', [ResidentController::class, 'me']);
        Route::post('/residents/avatar', [ResidentController::class, 'updateAvatar']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/residents', [ResidentController::class, 'index']);
        Route::post('/residents/bulk-approve', [ResidentController::class, 'bulkApprove']);
        Route::post('/residents/bulk-reject', [ResidentController::class, 'bulkReject']);
        Route::post('/residents', [ResidentController::class, 'store']);
        Route::delete('/residents/{id}', [ResidentController::class, 'destroy']);
        Route::post('/residents/{id}/approve', [ResidentController::class, 'approve']);
        Route::delete('/residents/{id}/reject', [ResidentController::class, 'reject']);
        Route::post('/residents/{id}/remind', [ResidentController::class, 'remind']);
    });

    // CSV exports (admin/officer)
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/export/residents', [ExportController::class, 'residents']);
        Route::get('/export/issues', [ExportController::class, 'issues']);
        Route::get('/export/payments', [ExportController::class, 'payments']);
        Route::get('/export/hoa-expenses', [ExportController::class, 'hoaExpenses']);
        // Bulk import residents/households from CSV.
        Route::post('/import/residents', [ResidentController::class, 'import']);
    });
    // Residents may update their OWN profile (controller enforces self-only).
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::put('/residents/{id}', [ResidentController::class, 'update']);
        Route::get('/residents/{id}/statement', [ExportController::class, 'residentStatement']);
    });

    // Households
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/households', [ResidentController::class, 'households']);
        Route::post('/households', [ResidentController::class, 'storeHousehold']);
        Route::put('/households/{id}', [ResidentController::class, 'updateHousehold']);
        Route::delete('/households/{id}', [ResidentController::class, 'destroyHousehold']);
    });

    // Payment Statuses
    // Removed: payment tracking/status removed from the system.

    // Household Members — residents manage their own household; staff may manage any.
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/household-members', [ResidentController::class, 'members']);
        Route::post('/household-members', [ResidentController::class, 'storeMember']);
        Route::put('/household-members/{id}', [ResidentController::class, 'updateMember']);
        Route::delete('/household-members/{id}', [ResidentController::class, 'destroyMember']);
    });

    // Announcements
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements/{id}/view', [AnnouncementController::class, 'markViewed']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
        Route::patch('/announcements/{id}/archive', [AnnouncementController::class, 'archive']);
        Route::patch('/announcements/{id}/publish', [AnnouncementController::class, 'publish']);
    });

    // Announcement Templates
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/announcement-templates', [AnnouncementTemplateController::class, 'index']);
    });
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/announcement-templates', [AnnouncementTemplateController::class, 'store']);
        Route::put('/announcement-templates/{id}', [AnnouncementTemplateController::class, 'update']);
        Route::delete('/announcement-templates/{id}', [AnnouncementTemplateController::class, 'destroy']);
    });

    // Report Templates & issued documents — staff only (residents have no reports).
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/report-templates', [ReportController::class, 'index']);
        Route::post('/report-templates', [ReportController::class, 'store']);
        Route::put('/report-templates/{id}', [ReportController::class, 'update']);
        Route::delete('/report-templates/{id}', [ReportController::class, 'destroy']);

        Route::get('/reports', [ReportController::class, 'staffIndex']);
        Route::post('/reports/issue', [ReportController::class, 'issue']);
    });

    // Issues
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/issues/my', [IssueController::class, 'myIssues']);
        Route::get('/issues/my/{id}/timeline', [IssueController::class, 'myTimeline']);
        Route::post('/issues', [IssueController::class, 'store']);
        Route::post('/issues/{id}/details', [IssueController::class, 'addDetails']);
    });
    // Issue media (photos/documents) lives on the private disk — owner or staff only.
    Route::get('/issues/media/{id}', [IssueController::class, 'media'])
        ->middleware('portal.role:admin,officer,resident');
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/issues/urgent-check', [IssueController::class, 'urgentCheck']);
        Route::get('/issues/common', [IssueController::class, 'common']);
        Route::get('/issues/{id}/history', [IssueController::class, 'history']);
        Route::get('/issues', [IssueController::class, 'index']);
        Route::put('/issues/{id}/status', [IssueController::class, 'updateStatus']);
        Route::put('/issues/{id}/priority', [IssueController::class, 'setPriority']);
        Route::put('/issues/{id}/assign', [IssueController::class, 'assign']);
        Route::patch('/issues/bulk-archive', [IssueController::class, 'bulkArchive']);
        Route::patch('/issues/{id}/archive', [IssueController::class, 'archive']);
        Route::post('/issues/{id}/respond', [IssueController::class, 'respond']);
    });

    // Financial — removed (payment/dues/financial tracking removed from the system).

    // Families
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::put('/families/{id}', [FamilyController::class, 'update']);
        Route::delete('/families/{id}', [FamilyController::class, 'destroy']);
    });

    // Recommendations
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/recommendations/my', [RecommendationController::class, 'myRecs']);
        Route::post('/recommendations', [RecommendationController::class, 'store']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/recommendations', [RecommendationController::class, 'index']);
        Route::put('/recommendations/{id}/status', [RecommendationController::class, 'updateStatus']);
    });

    // Community events / calendar
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/events', [EventController::class, 'index']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
    });
    Route::post('/events/{id}/rsvp', [EventController::class, 'rsvp'])
        ->middleware('portal.role:resident');

    // Officers
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/officers', [OfficerController::class, 'index']);
        Route::post('/officers', [OfficerController::class, 'store']);
        Route::post('/officers/avatar', [OfficerController::class, 'updateAvatar']);
        Route::put('/officers/{id}', [OfficerController::class, 'update']);
        Route::delete('/officers/{id}', [OfficerController::class, 'destroy']);
    });

    // Messages  (static routes MUST come before wildcard {convId})
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/messages/threads', [MessageController::class, 'threads']);
        Route::get('/messages/officers', [MessageController::class, 'officers']);
        Route::get('/messages/residents', [MessageController::class, 'residents'])
            ->middleware('portal.role:admin,officer');
        Route::get('/messages/requests', [MessageController::class, 'requests']);
        Route::post('/messages/start', [MessageController::class, 'start']);
        Route::post('/messages/{convId}/accept', [MessageController::class, 'accept']);
        Route::post('/messages/{convId}/reject', [MessageController::class, 'reject']);
        Route::get('/messages/{convId}', [MessageController::class, 'show']);
        Route::post('/messages/{convId}', [MessageController::class, 'send']);
        Route::delete('/messages/{convId}', [MessageController::class, 'destroy']);
    });

    // Officer Files
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/officer-files', [OfficerFileController::class, 'index']);
        Route::post('/officer-files', [OfficerFileController::class, 'store']);
        Route::get('/officer-files/{id}/download', [OfficerFileController::class, 'download']);
        Route::patch('/officer-files/{id}/archive', [OfficerFileController::class, 'archive']);
        Route::delete('/officer-files/{id}', [OfficerFileController::class, 'destroy']);
    });

    // HOA Expenses Transparency — how fees are utilized (all roles view; admin records)
    Route::get('/hoa-expenses', [HoaExpenseController::class, 'index'])
        ->middleware('portal.role:admin,officer,resident');
    Route::get('/hoa-expenses/{id}/receipt', [HoaExpenseController::class, 'receipt'])
        ->middleware('portal.role:admin,officer,resident');
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/hoa-expenses', [HoaExpenseController::class, 'store']);
        Route::put('/hoa-expenses/{id}', [HoaExpenseController::class, 'update']);
        Route::delete('/hoa-expenses/{id}', [HoaExpenseController::class, 'destroy']);
    });

    // Officer Expense Requests (staff claim submission + 1-click admin approval)
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/expense-requests', [ExpenseRequestController::class, 'index']);
        Route::get('/expense-requests/{id}/receipt', [ExpenseRequestController::class, 'receipt']);
        Route::delete('/expense-requests/{id}', [ExpenseRequestController::class, 'destroy']);
    });
    Route::middleware('portal.role:officer')->group(function () {
        Route::post('/expense-requests', [ExpenseRequestController::class, 'store']);
    });
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/expense-requests/{id}/approve', [ExpenseRequestController::class, 'approve']);
        Route::post('/expense-requests/{id}/reject', [ExpenseRequestController::class, 'reject']);
    });

    // GCash payments: residents submit + view own; staff manage the queue; admin validates
    Route::middleware('portal.role:resident')->group(function () {
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/my', [PaymentController::class, 'my']);
    });
    // Community finance summary — visible to every portal (admin dashboard reads it too).
    Route::get('/transparency/summary', [PaymentController::class, 'transparency'])
        ->middleware('portal.role:admin,officer,resident');
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index']);
    });
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/payments/{id}/validate', [PaymentController::class, 'validatePayment']);
    });
    // Receipts streamed to the owner or staff only (controller enforces access)
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/payments/{id}/receipt', [PaymentController::class, 'receipt']);
        Route::get('/payments/{id}/official-receipt', [PaymentController::class, 'officialReceipt']);
    });
    // HOA Policies - all roles can view; admin manages
    Route::get('/hoa-policies', [HoaPolicyController::class, 'index'])
        ->middleware('portal.role:admin,officer,resident');
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/hoa-policies', [HoaPolicyController::class, 'store']);
        Route::put('/hoa-policies/{id}', [HoaPolicyController::class, 'update']);
        Route::delete('/hoa-policies/{id}', [HoaPolicyController::class, 'destroy']);
    });

    // Ledger — statements + aging (single source of truth for balances)
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/ledger/my', [LedgerController::class, 'my']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/ledger/aging', [LedgerController::class, 'aging']);
        Route::get('/ledger/{residentId}', [LedgerController::class, 'show']);
    });

    // Assessments (dues schedule) — staff read; admin manages
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/assessments', [AssessmentController::class, 'index']);
    });
    Route::middleware('portal.role:admin')->group(function () {
        Route::post('/assessments', [AssessmentController::class, 'store']);
        Route::post('/assessments/generate', [AssessmentController::class, 'generate']);
        Route::put('/assessments/{id}', [AssessmentController::class, 'update']);
        Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy']);

        // Category budgets
        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::put('/budgets/{id}', [BudgetController::class, 'update']);
        Route::delete('/budgets/{id}', [BudgetController::class, 'destroy']);
    });

    // GCash Payment Settings - admin changes the receiving account/QR; all roles can read it
    Route::get('/gcash-settings', [GcashSettingController::class, 'show'])
        ->middleware('portal.role:admin,officer,resident');
    Route::post('/gcash-settings', [GcashSettingController::class, 'update'])
        ->middleware('portal.role:admin');
});
