<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/home', HomeController::class);
Route::apiResource('portfolio-cards', PortfolioController::class);
Route::get('/experience', [ExperienceController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
