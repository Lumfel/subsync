<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\StatusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('layouts.main');
})->name('dashboard');

/* AUTH */
Route::get('/login', function () {
    return view('layouts.login_1');
})->name('login_1');
Route::post('/login', [AuthController::class, 'login']);
/* STATIC PAGES */
Route::get('/analytics', function () {
    return view('layouts.analytics');
})->name('analytics');

Route::get('/finance', function () {
    return view('layouts.finance');
})->name('finance');

Route::get('/members', function () {
    return view('layouts.members');
})->name('members');

Route::get('/deliquents', function () {
    return view('layouts.deliquents');
})->name('delinquents');

Route::get('/residents', function () {
    return view('layouts.residents');
})->name('residents');

Route::get('/mapping', function () {
    return view('layouts.mapping');
})->name('mapping');

use App\Models\User;
use App\Models\Family;
use App\Models\Household;
use App\Models\Member;

Route::get('/manage_users', function () {
    return view('layouts.manage_users', [
        'users' => User::all(),
        'families' => Family::all(),
        'households' => Household::all(),
        'members' => Member::all(),
    ]);
});

Route::get('/reports', function () {
    return view('layouts.reports');
})->name('reports');

/* CRUD */
Route::post('/households', [HouseholdController::class, 'store']);
Route::delete('/households/{id}', [HouseholdController::class, 'destroy']);
Route::post('/members', [MemberController::class, 'store']);
Route::post('/statuses', [StatusController::class, 'store']);