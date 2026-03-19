<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/training/overview', function () {
    return view('training.overview');
});

Route::get('/training/phase1', function () {
    return view('training.phase1');
});

Route::get('/training/phase2', function () {
    return view('training.phase2');
});

Route::get('/training/phase3', function () {
    return view('training.phase3');
});

Route::get('/training/phase4', function () {
    return view('training.phase4');
});
