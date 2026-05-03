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

Route::get('/manage_users', function () {
    return view('layouts.manage_users');
})->name('manage_users');

Route::get('/reports', function () {
    return view('layouts.reports');
})->name('reports');

/* CRUD */
Route::post('/households', [HouseholdController::class, 'store']);
Route::post('/members', [MemberController::class, 'store']);
Route::post('/statuses', [StatusController::class, 'store']);