<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerFileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ResidentController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Resident / Officer login
Route::get('/login', function () {
    return view('layouts.login_residen_officers');
})->name('login');

Route::post('/login', [AuthController::class, 'residentLogin'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Admin login
Route::get('/admin/login', function () {
    return view('layouts.login_admin');
})->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');

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
    $household = $resident?->house_id
        ? \App\Models\Household::query()->find($resident->house_id)
        : null;
    return view('layouts.Residents', [
        'residentName'    => $resident?->name ?? 'Resident',
        'residentId'      => $resident?->id,
        'houseId'         => $resident?->house_id,
        'blockLot'        => $household?->block_lot_number ?? '',
        'residentBalance' => $resident?->current_balance ?? 0,
    ]);
})->name('residents')->middleware('auth:resident');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ═══ API ROUTES ═══

// Combined dashboard data (single request replaces 11+ parallel calls)
Route::get('/api/dashboard-data', [DashboardController::class, 'data']);

// Map API
Route::prefix('api/map')->group(function () {
    Route::get('/households', [MapController::class, 'households']);
    Route::get('/facilities', [MapController::class, 'facilities']);
    Route::get('/issues', [MapController::class, 'issues']);
    Route::post('/facility', [MapController::class, 'storeFacility']);
    Route::put('/facility/{id}', [MapController::class, 'updateFacility']);
    Route::delete('/facility/{id}', [MapController::class, 'deleteFacility']);
    Route::put('/household/{id}/location', [MapController::class, 'updateHouseholdLocation']);
});

// Residents & Households API
Route::prefix('api')->group(function () {
    // Stats
    Route::get('/stats', [ResidentController::class, 'stats']);

    // Residents
    Route::get('/residents', [ResidentController::class, 'index']);
    Route::get('/residents/me', [ResidentController::class, 'me']);
    Route::post('/residents', [ResidentController::class, 'store']);
    Route::put('/residents/{id}', [ResidentController::class, 'update']);
    Route::delete('/residents/{id}', [ResidentController::class, 'destroy']);
    Route::post('/residents/{id}/approve', [ResidentController::class, 'approve']);
    Route::delete('/residents/{id}/reject', [ResidentController::class, 'reject']);

    // Households
    Route::get('/households', [ResidentController::class, 'households']);
    Route::post('/households', [ResidentController::class, 'storeHousehold']);
    Route::put('/households/{id}', [ResidentController::class, 'updateHousehold']);
    Route::delete('/households/{id}', [ResidentController::class, 'destroyHousehold']);

    // Delinquents
    Route::get('/delinquents', [ResidentController::class, 'delinquents']);

    // Household Members
    Route::get('/household-members', [ResidentController::class, 'members']);
    Route::post('/household-members', [ResidentController::class, 'storeMember']);
    Route::put('/household-members/{id}', [ResidentController::class, 'updateMember']);
    Route::delete('/household-members/{id}', [ResidentController::class, 'destroyMember']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    Route::post('/announcements/{id}/view', [AnnouncementController::class, 'markViewed']);

    // Issues
    Route::get('/issues/urgent-check', [IssueController::class, 'urgentCheck']);
    Route::get('/issues', [IssueController::class, 'index']);
    Route::get('/issues/my', [IssueController::class, 'myIssues']);
    Route::post('/issues', [IssueController::class, 'store']);
    Route::put('/issues/{id}/status', [IssueController::class, 'updateStatus']);
    Route::post('/issues/{id}/respond', [IssueController::class, 'respond']);

    // Financial
    Route::get('/financial', [FinancialController::class, 'index']);
    Route::get('/financial/my', [FinancialController::class, 'myRecords']);
    Route::get('/financial/summary', [FinancialController::class, 'summary']);
    Route::get('/financial/payments', [FinancialController::class, 'payments']);
    Route::post('/financial', [FinancialController::class, 'store']);
    Route::delete('/financial/{id}', [FinancialController::class, 'destroy']);

    // Financial Reports (monthly treasurer receipts)
    Route::get('/financial-reports', [FinancialController::class, 'reportIndex']);
    Route::post('/financial-reports', [FinancialController::class, 'reportStore']);
    Route::delete('/financial-reports/{id}', [FinancialController::class, 'reportDestroy']);

    // Families
    Route::get('/families', [FamilyController::class, 'index']);
    Route::post('/families', [FamilyController::class, 'store']);
    Route::put('/families/{id}', [FamilyController::class, 'update']);
    Route::delete('/families/{id}', [FamilyController::class, 'destroy']);

    // Recommendations
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/recommendations/my', [RecommendationController::class, 'myRecs']);
    Route::post('/recommendations', [RecommendationController::class, 'store']);
    Route::put('/recommendations/{id}/status', [RecommendationController::class, 'updateStatus']);

    // Officers
    Route::get('/officers', [OfficerController::class, 'index']);
    Route::post('/officers', [OfficerController::class, 'store']);
    Route::put('/officers/{id}', [OfficerController::class, 'update']);
    Route::delete('/officers/{id}', [OfficerController::class, 'destroy']);

    // Messages  (static routes MUST come before wildcard {convId})
    Route::get('/messages/threads', [MessageController::class, 'threads']);
    Route::post('/messages/start', [MessageController::class, 'start']);
    Route::get('/messages/{convId}', [MessageController::class, 'show']);
    Route::post('/messages/{convId}', [MessageController::class, 'send']);

    // Officer Files
    Route::get('/officer-files', [OfficerFileController::class, 'index']);
    Route::post('/officer-files', [OfficerFileController::class, 'store']);
    Route::delete('/officer-files/{id}', [OfficerFileController::class, 'destroy']);
});
