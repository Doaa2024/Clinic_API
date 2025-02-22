<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SecretaryController;
use App\Http\Controllers\Api\PatientsController;
use App\Http\Controllers\Api\AppointmentsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\PrescriptionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::apiResource('/users', UserController::class)->middleware('auth:sanctum');
Route::get('dashboard/overview', [DashboardController::class, 'overview'])->middleware('auth:sanctum');
Route::get('dashboard/appointmentStatistics', [DashboardController::class, 'appointmentStatistics'])->middleware('auth:sanctum');
Route::get('dashboard/top5Doctors', [DashboardController::class, 'top5Doctors'])->middleware('auth:sanctum');
Route::get('dashboard/revenueReport', [DashboardController::class, 'revenueReport'])->middleware('auth:sanctum');
Route::get('dashboard/doctoroverview', [DashboardController::class, 'doctoroverview'])->middleware('auth:sanctum');
Route::get('dashboard/appointmentsStatus', [DashboardController::class, 'appointmentsStatus'])->middleware('auth:sanctum');
Route::get('dashboard/appointmentsOverMonths', [DashboardController::class, 'appointmentsOverMonths'])->middleware('auth:sanctum');
Route::apiResource('secretary', SecretaryController::class)->middleware('auth:sanctum');
Route::apiResource('patients', PatientsController::class)->middleware('auth:sanctum');
Route::apiResource('appointments', AppointmentsController::class)->middleware('auth:sanctum');
Route::apiResource('billings', BillingController::class)->middleware('auth:sanctum');
Route::apiResource('prescriptions', PrescriptionsController::class)->middleware('auth:sanctum');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:15,1');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('doctorAppointments', [AppointmentsController::class, 'getDoctorsAppointment'])->middleware('auth:sanctum');
