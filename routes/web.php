<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FamilyController;

use App\Models\User;
use App\Models\Family;
use App\Models\Household;
use App\Models\Member;
use App\Models\Delinquent;

/*
|--------------------------------------------------------------------------
| RESOURCE ROUTES
|--------------------------------------------------------------------------
*/
Route::resource('households', HouseholdController::class);
Route::resource('users', UserController::class);
Route::resource('families', FamilyController::class);
Route::resource('members', MemberController::class);
Route::resource('statuses', StatusController::class);


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('layouts.login_1');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $members = Member::all();
    $households = Household::all();

    return view('layouts.main', compact('members', 'households'));
})->name('dashboard');


/*
|--------------------------------------------------------------------------
| STATIC PAGES
|--------------------------------------------------------------------------
*/
Route::view('/analytics', 'layouts.analytics')->name('analytics');
Route::view('/finance', 'layouts.finance')->name('finance');
Route::view('/mapping', 'layouts.mapping')->name('mapping');
Route::view('/reports', 'layouts.reports')->name('reports');


/*
|--------------------------------------------------------------------------
| MEMBERS PAGE
|--------------------------------------------------------------------------
*/
Route::get('/members', function () {
    $members = Member::with(['user', 'household'])->get();

    return view('layouts.members', compact('members'));
})->name('members');


/*
|--------------------------------------------------------------------------
| DELINQUENTS PAGE
|--------------------------------------------------------------------------
*/
Route::get('/deliquents', function () {
    $delinquents = Delinquent::with('household')->get();

    return view('layouts.deliquents', compact('delinquents'));
})->name('delinquents');


/*
|--------------------------------------------------------------------------
| RESIDENTS PAGE
|--------------------------------------------------------------------------
*/
Route::get('/residents', function () {
    $households = Household::with([
        'family',
        'statuses',
        'delinquents',
        'householdMembers'
    ])->get();

    return view('layouts.residents', compact('households'));
})->name('residents');


/*
|--------------------------------------------------------------------------
| MANAGE USERS
|--------------------------------------------------------------------------
*/
Route::get('/manage_users', function () {
    return view('layouts.manage_users', [
        'users' => User::all(),
        'families' => Family::all(),
        'households' => Household::all(),
        'members' => Member::with(['user', 'household'])->get(),
    ]);
})->name('manage_users');


/*
|--------------------------------------------------------------------------
| TEST ROUTE
|--------------------------------------------------------------------------
*/
Route::get('/test-relations', function () {
    $household = Household::with([
        'family',
        'statuses',
        'delinquents',
        'householdMembers'
    ])->first();

    return $household;
});