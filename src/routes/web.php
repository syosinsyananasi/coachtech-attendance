<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;

// 一般ユーザー（認証 + メール認証済み）
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail']);
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update']);
});

// 修正申請一覧（一般/管理者共用URL、ガードで分岐）
Route::get('/correction_request/list', [CorrectionRequestController::class, 'list'])
    ->middleware('auth:admin,web');

// 修正申請承認（管理者のみ）
Route::middleware('auth:admin')->group(function () {
    Route::get('/correction_request/approve/{id}', [CorrectionRequestController::class, 'approve']);
    Route::post('/correction_request/approve/{id}', [CorrectionRequestController::class, 'storeApproval']);
});

// 管理者認証
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin']);
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list']);
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance']);
        Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'staffCsv']);
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail']);
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update']);
        Route::get('/staff/list', [AdminAttendanceController::class, 'staffList']);
    });
});
