<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/settings', function () {
    return view('settings');
});

Route::get('/batches', function () {
    return view('batches');
});

Route::get('/plans', function () {
    return view('plans');
});

Route::get('/attendance', function () {
    return view('attendance');
});

Route::get('/diet-plans', function () {
    return view('diet-plans');
});

Route::get('/workout-plans', function () {
    return view('workout-plans');
});

Route::get('/staff', function () {
    return view('staff-trainers');
});

Route::get('/members', function () {
    return view('members');
});

Route::get('/reports', function () {
    return view('reports');
});

Route::get('/payments', function () {
    return view('payments');
});

Route::get('/expenses', function () {
    return view('expenses');
});
