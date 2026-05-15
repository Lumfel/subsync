<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('layouts.login_residen_officers');
})->name('login');
