<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationRequestController;

Route::post('/register', [AuthController::class, "register"]);
Route::post('/login', [AuthController::class, "login"]);
Route::post('/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');

Route::post('/get-user', [UserController::class, "getUserInfo"])->middleware('auth:sanctum');

Route::get('/get-vacation-overview', [VacationRequestController::class, "getVacationBalance"])->middleware('auth:sanctum');
Route::post('/create-request', [VacationRequestController::class, "createRequest"])->middleware('auth:sanctum');
Route::get('/get-requests', [VacationRequestController::class, "getUserRequests"])->middleware('auth:sanctum');
Route::get('/company-holidays', [VacationRequestController::class, "getCompanyHolidays"])->middleware('auth:sanctum');
Route::post('/cancel-request/{id}', [VacationRequestController::class, 'cancelRequest'])->middleware('auth:sanctum');
