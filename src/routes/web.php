<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;

Route::get('/', function () {
    return redirect()->route('login');
});

// 一般ユーザー（認証 + メール認証済み）
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'stamp'])->name('attendance.stamp');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'redirectByDate'])->name('attendance.redirectByDate');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

// 修正申請一覧（一般/管理者共用URL、ガードで分岐）
Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->middleware('auth:admin,web')
    ->name('correction_request.index');

// 修正申請承認（管理者のみ）
Route::middleware('auth:admin')->group(function () {
    Route::get('/stamp_correction_request/approve/{id}', [CorrectionRequestController::class, 'show'])
        ->name('correction_request.show');
    Route::post('/stamp_correction_request/approve/{id}', [CorrectionRequestController::class, 'approve'])
        ->name('correction_request.approve');
});

// 管理者認証
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('admin.login.create');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaff'])->name('admin.staff.show');
        Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.staff.exportCsv');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        Route::get('/staff/list', [AdminAttendanceController::class, 'indexStaff'])->name('admin.staff.index');
    });
});
