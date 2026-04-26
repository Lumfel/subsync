<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.main');
});

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

route::get('/mapping', function(){
return view('layouts.mapping');
})->name('mapping');

route::get('/manage_users', function(){
    return View('layouts.manage_users');
})->name('Manage users');

route::get('/reports', function(){
    return View('layouts.reports');
})->name('reports');