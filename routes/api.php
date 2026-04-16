<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EducationEntryController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\SkillGroupController;
use Illuminate\Support\Facades\Route;

Route::get('/home', HomeController::class);
Route::apiResource('portfolio-cards', PortfolioController::class);
Route::apiResource('skill-groups', SkillGroupController::class);
Route::apiResource('education-entries', EducationEntryController::class);
Route::get('/experience', [ExperienceController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
