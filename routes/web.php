<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerFileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HoaExpenseController;
use App\Http\Controllers\HoaPolicyController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

// Public landing page
Route::get('/', function () {
    return view('landing');
});

// Resident / Officer login
Route::get('/login', function () {
    return view('layouts.login_residen_officers');
})->name('login');

Route::post('/login', [AuthController::class, 'residentLogin'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

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
        'adminId'   => session('admin_id'),
    ]);
})->name('dashboard')->middleware('admin.auth');

Route::get('/officer-portal', function () {
    $officer = \Illuminate\Support\Facades\Auth::guard('officer')->user();
    return view('layouts.officer-portal', [
        'officerName' => $officer?->name ?? 'Officer',
        'officerId'   => $officer?->id,
        'officerRole' => $officer?->role_description ?? 'HOA Officer',
    ]);
})->name('officer.portal')->middleware('officer.auth');

Route::get('/residents', function () {
    $resident = \Illuminate\Support\Facades\Auth::guard('resident')->user();
    request()->session()->forget(['admin_id', 'admin_name']);
    request()->session()->put('active_role', 'resident');
    request()->session()->put('resident_id', $resident?->id);
    $household = $resident?->house_id
        ? \App\Models\Household::query()->find($resident->house_id)
        : null;
    return view('layouts.residents', [
        'residentName'    => $resident?->name ?? 'Resident',
        'residentId'      => $resident?->id,
        'houseId'         => $resident?->house_id,
        'blockLot'        => $household?->block_lot_number ?? '',
    ]);
})->name('residents')->middleware('auth:resident');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ═══ API ROUTES ═══

// Combined dashboard data (single request replaces 11+ parallel calls)
Route::middleware('portal.role:admin,officer')->group(function () {
    Route::get('/api/dashboard-data', [DashboardController::class, 'data']);
});

// Server-Sent Events — instant push for announcements & issues (admin + officer + resident)
Route::middleware('portal.role:admin,officer,resident')->group(function () {
    Route::get('/api/stream/updates', [StreamController::class, 'updates']);
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
Route::middleware('portal.role:admin,officer')->group(function () {
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
    Route::get('/stats', [ResidentController::class, 'stats']);

    // Combined portal data (single request replaces 6+ parallel calls)
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/resident/portal-data', [ResidentController::class, 'portalData']);
        Route::get('/residents/me', [ResidentController::class, 'me']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/residents', [ResidentController::class, 'index']);
        Route::post('/residents', [ResidentController::class, 'store']);
        Route::delete('/residents/{id}', [ResidentController::class, 'destroy']);
        Route::post('/residents/{id}/approve', [ResidentController::class, 'approve']);
        Route::delete('/residents/{id}/reject', [ResidentController::class, 'reject']);
    });
    // Residents may update their OWN profile (controller enforces self-only).
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::put('/residents/{id}', [ResidentController::class, 'update']);
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

    // Household Members
    Route::middleware('portal.role:resident')->group(function () {
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
    });

    // Issues
    Route::middleware('portal.role:resident')->group(function () {
        Route::get('/issues/my', [IssueController::class, 'myIssues']);
        Route::post('/issues', [IssueController::class, 'store']);
    });
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/issues/urgent-check', [IssueController::class, 'urgentCheck']);
        Route::get('/issues', [IssueController::class, 'index']);
        Route::put('/issues/{id}/status', [IssueController::class, 'updateStatus']);
        Route::put('/issues/{id}/priority', [IssueController::class, 'setPriority']);
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

    // Officers
    Route::middleware('portal.role:admin,officer')->group(function () {
        Route::get('/officers', [OfficerController::class, 'index']);
        Route::post('/officers', [OfficerController::class, 'store']);
        Route::put('/officers/{id}', [OfficerController::class, 'update']);
        Route::delete('/officers/{id}', [OfficerController::class, 'destroy']);
    });

    // Messages  (static routes MUST come before wildcard {convId})
    Route::middleware('portal.role:admin,officer,resident')->group(function () {
        Route::get('/messages/threads', [MessageController::class, 'threads']);
        Route::post('/messages/start', [MessageController::class, 'start']);
        Route::get('/messages/{convId}', [MessageController::class, 'show']);
        Route::post('/messages/{convId}', [MessageController::class, 'send']);
        Route::delete('/messages/{convId}', [MessageController::class, 'destroy']);
    });

    // Officer Files
    Route::middleware('portal.role:officer')->group(function () {
        Route::get('/officer-files', [OfficerFileController::class, 'index']);
        Route::post('/officer-files', [OfficerFileController::class, 'store']);
        Route::delete('/officer-files/{id}', [OfficerFileController::class, 'destroy']);
    });

    // HOA Expenses Transparency - Residents can view how HOA fees are being utilized
    Route::prefix('hoa-expenses')->middleware('portal.role:resident')->group(function () {
        Route::get('/', [HoaExpenseController::class, 'index']);
    });
    // HOA Policies - Residents can view community rules and regulations
    Route::prefix("hoa-policies")->middleware("portal.role:resident")->group(function () {
        Route::get("/", [HoaPolicyController::class, "index"]);
    });
});