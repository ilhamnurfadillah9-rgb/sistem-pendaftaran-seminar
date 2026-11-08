<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeminarController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\SeminarController as PublicSeminarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSeminarController::class, 'index'])->name('home');
Route::get('/seminar/{seminar}', [PublicSeminarController::class, 'show'])->name('seminar.show');
Route::post('/seminar/{seminar}/pendaftaran', [RegistrationController::class, 'store'])
    ->name('registrations.store');
Route::get('/pendaftaran/{token}', [RegistrationController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('registrations.show');

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('/login', [AuthController::class, 'create'])->name('login');
            Route::post('/login', [AuthController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('login.store');
        });

        Route::middleware('auth')->group(function (): void {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
            Route::get('/attendance/scan', [AttendanceController::class, 'scan'])
                ->name('attendance.scan');
            Route::post('/attendance/scan', [AttendanceController::class, 'storeScan'])
                ->middleware('throttle:attendance-scan')
                ->name('attendance.scan.store');
            Route::resource('seminars', SeminarController::class);
            Route::prefix('seminars/{seminar}')
                ->name('seminars.')
                ->scopeBindings()
                ->group(function (): void {
                    Route::get('/attendance', [AttendanceController::class, 'index'])
                        ->name('attendance.index');
                    Route::put('/attendance/{registration}', [AttendanceController::class, 'update'])
                        ->name('attendance.update');
                    Route::get('/participants', [ParticipantController::class, 'index'])
                        ->name('participants.index');
                    Route::get('/participants/{registration}/edit', [ParticipantController::class, 'edit'])
                        ->name('participants.edit');
                    Route::put('/participants/{registration}', [ParticipantController::class, 'update'])
                        ->name('participants.update');
                    Route::delete('/participants/{registration}', [ParticipantController::class, 'destroy'])
                        ->name('participants.destroy');
                    Route::get('/participants/{registration}/qr', [ParticipantController::class, 'qr'])
                        ->name('participants.qr');
                });
            Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        });
    });
