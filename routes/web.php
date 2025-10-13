<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\StudentReportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Главная страница
Route::get('/', function () {
    return view('welcome');
});

// Маршруты для управления приглашениями
Route::middleware(['auth'])->group(function () {
    Route::resource('invitations', InvitationController::class);
});

// Маршруты для справочной системы
Route::middleware(['auth'])->prefix('help')->name('help.')->group(function () {
    Route::get('/', [HelpController::class, 'index'])->name('index');
    Route::get('/admin', [HelpController::class, 'admin'])->name('admin');
    Route::get('/curator', [HelpController::class, 'curator'])->name('curator');
    Route::get('/student', [HelpController::class, 'student'])->name('student');
    Route::get('/qr-codes', [HelpController::class, 'qrCodes'])->name('qr_codes');
    Route::get('/reports', [HelpController::class, 'reports'])->name('reports');
    Route::get('/export', [HelpController::class, 'export'])->name('export');
});

// Маршруты для управления пользователями
Route::middleware(['auth'])->group(function () {
    // Профиль пользователя (доступен всем аутентифицированным пользователям)
    Route::get('/profile/user', [UserController::class, 'profile'])->name('profile'); // Изменен путь, чтобы избежать конфликта
    Route::put('/profile/user', [UserController::class, 'updateProfile'])->name('profile.update.user'); // Изменено имя маршрута
    
    // Управление пользователями (доступно только администраторам)
    Route::middleware(['check.role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
    });
});

// Маршруты для аутентифицированных пользователей
Route::middleware(['auth'])->group(function () {
    // Дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Маршруты для профиля
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Маршруты для темы
    Route::post('/toggle-theme', [ThemeController::class, 'toggle'])->name('toggle-theme');

    // Маршруты для мероприятий (доступны всем аутентифицированным)
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/attendance/manual', [EventController::class, 'manualAttendanceForm'])->name('events.manual.attendance.form');
    Route::post('/events/attendance/manual', [EventController::class, 'manualAttendance'])->name('events.manual.attendance');
    Route::get('/events/attendance/verify/{token}', [EventController::class, 'verifyAttendance'])->name('events.attendance.verify');


        // Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update')->where('event', '[0-9]+');


        
// check.role:curator
        

// Маршруты для отчетов (доступно для админов и кураторов)
    // Route::middleware(['check.role:curator'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/group_attendance', [ReportController::class, 'groupAttendance'])->name('reports.group_attendance');
        Route::get('/reports/student-attendance', [ReportController::class, 'studentAttendance'])->name('reports.student_attendance');
        
        // Маршруты для экспорта отчетов по группам
        Route::get('/reports/group-attendance/export-csv', [ReportController::class, 'exportGroupAttendanceCsv'])->name('reports.exportGroupAttendanceCsv');
        Route::get('/reports/group-attendance/export-excel', [ReportController::class, 'exportGroupAttendanceExcel'])->name('reports.exportGroupAttendanceExcel');
        Route::get('/reports/group-attendance/export-pdf', [ReportController::class, 'exportGroupAttendancePdf'])->name('reports.exportGroupAttendancePdf');
        
        // Маршруты для экспорта отчетов по студентам
        Route::get('/reports/student-attendance/export-csv', [ReportController::class, 'exportStudentAttendanceCsv'])->name('reports.exportStudentAttendanceCsv');
        Route::get('/reports/student-attendance/export-excel', [ReportController::class, 'exportStudentAttendanceExcel'])->name('reports.exportStudentAttendanceExcel');
        Route::get('/reports/student-attendance/export-pdf', [ReportController::class, 'exportStudentAttendancePdf'])->name('reports.exportStudentAttendancePdf');
        
        // Новые маршруты для аналитики и экспорта
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
        Route::get('/reports/export-word', [ReportController::class, 'exportWord'])->name('reports.export_word');

        // Маршруты для аналитики и отчетов
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
Route::get('/reports/export-word', [ReportController::class, 'exportWord'])->name('reports.export_word');

// Маршруты для аналитики по студентам
Route::get('/reports/students', [StudentReportController::class, 'index'])->name('reports.students');
Route::get('/reports/students/export-pdf/{id}', [StudentReportController::class, 'exportStudentPdf'])->name('reports.student.export_pdf');
Route::get('/reports/students/export-excel/{id}', [StudentReportController::class, 'exportStudentExcel'])->name('reports.student.export_excel');
Route::get('/reports/students/export-word/{id}', [StudentReportController::class, 'exportStudentWord'])->name('reports.student.export_word');

// Маршруты для рейтинга студентов
Route::get('/reports/ranking', [StudentReportController::class, 'ranking'])->name('reports.students.ranking');
Route::get('/reports/ranking/export-pdf', [StudentReportController::class, 'exportRankingPdf'])->name('reports.ranking.export_pdf');
Route::get('/reports/ranking/export-excel', [StudentReportController::class, 'exportRankingExcel'])->name('reports.ranking.export_excel');


    // Маршруты для отчетов
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/group_attendance', [ReportController::class, 'groupAttendance'])->name('reports.group_attendance');
    Route::get('/reports/student-attendance', [ReportController::class, 'studentAttendance'])->name('reports.student_attendance');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
   
    Route::get('/events/{event}/attendance', [EventController::class, 'manageAttendance'])->name('events.attendance')->where('event', '[0-9]+');
        Route::post('/events/{event}/attendance', [EventController::class, 'storeAttendance'])->name('events.attendance.store')->where('event', '[0-9]+');

    // });




    // Маршруты для администратора
    Route::middleware(['check.role:admin'])->group(function () { // Унифицировано использование middleware
        // Создание и отчеты
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/report', [EventController::class, 'report'])->name('events.report');
        Route::get('/events/report/pdf', [EventController::class, 'reportPdf'])->name('events.report.pdf');

        // Управление мероприятиями
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit')->where('event', '[0-9]+');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update')->where('event', '[0-9]+');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy')->where('event', '[0-9]+');
   
   
        // Маршруты для отчетов
        // Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        // Route::get('/reports/group_attendance', [ReportController::class, 'groupAttendance'])->name('reports.group_attendance');
        // Route::get('/reports/student-attendance', [ReportController::class, 'studentAttendance'])->name('reports.student_attendance');
        // Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
        // Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
        // Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');

        //     Route::get('/events/{event}/attendance', [EventController::class, 'manageAttendance'])->name('events.attendance')->where('event', '[0-9]+');
        // Route::post('/events/{event}/attendance', [EventController::class, 'storeAttendance'])->name('events.attendance.store')->where('event', '[0-9]+');

    });
    
    // Маршруты для администраторов и кураторов
    // Используем auth middleware вместо check.role
    Route::middleware(['auth'])->group(function () {
        // Управление посещаемостью

        // QR-код для отметки посещаемости
        Route::get('/events/{event}/qrcode', [EventController::class, 'showQrCode'])
            ->name('events.qrcode');

        // Скачивание QR-кода
        Route::get('/events/{event}/qrcode/download', [EventController::class, 'downloadQrCode'])
            ->name('events.qrcode.download');
});
// Проверка QR-кода и отметка посещаемости (доступно для всех)
Route::get('/attendance/verify/{token}', [EventController::class, 'verifyAttendance'])
    ->name('events.attendance.verify');


    // Общие маршруты для мероприятий (после админских)
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show')->where('event', '[0-9]+');
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register')->where('event', '[0-9]+');
    Route::delete('/events/{event}/unregister', [EventController::class, 'unregister'])->name('events.unregister')->where('event', '[0-9]+');
});

// Маршрут для гостевой темы (вне группы auth)
Route::post('/toggle-theme-guest', [ThemeController::class, 'toggleGuest'])->name('toggle-theme-guest');

// Подключение маршрутов аутентификации
require __DIR__ . '/auth.php';
