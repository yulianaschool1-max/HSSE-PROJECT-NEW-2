<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Modules\DailyWorkController;
use App\Http\Controllers\Modules\HSSEPlanController;
use App\Http\Controllers\Modules\EventController;
use App\Http\Controllers\Modules\InventoryController;
use App\Http\Controllers\Modules\GasDetectorController;
use App\Http\Controllers\Modules\WorkforceController;
use App\Http\Controllers\Modules\ManHoursController;
use App\Http\Controllers\Modules\IncidentController;
use App\Http\Controllers\Modules\IssueFollowUpController;
use App\Http\Controllers\Modules\UserController;

/*
|--------------------------------------------------------------------------
| Halaman Login & Autentikasi
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export')->middleware('can.export');
});

/*
|--------------------------------------------------------------------------
| Daily Work
|--------------------------------------------------------------------------
*/

Route::prefix('daily-work')->name('daily-work.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role: Admin Management, Admin Inventory, User)
    Route::get('/', [DailyWorkController::class, 'index'])->name('index');
    Route::get('/check-duplicate', [DailyWorkController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::get('/export', [DailyWorkController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/masters/all', [DailyWorkController::class, 'getMasters'])->name('masters.index');
    Route::get('/{daily_work}', [DailyWorkController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/', [DailyWorkController::class, 'store'])->name('store');
        Route::put('/{daily_work}', [DailyWorkController::class, 'update'])->name('update');
        Route::delete('/{daily_work}', [DailyWorkController::class, 'destroy'])->name('destroy');

        // Master Data CRUD (Perusahaan, PTW, SIKKA)
        Route::post('/masters/companies', [DailyWorkController::class, 'storeCompany'])->name('masters.companies.store');
        Route::put('/masters/companies/{id}', [DailyWorkController::class, 'updateCompany'])->name('masters.companies.update');
        Route::delete('/masters/companies/{id}', [DailyWorkController::class, 'destroyCompany'])->name('masters.companies.destroy');
        Route::post('/masters/companies/{id}/toggle', [DailyWorkController::class, 'toggleCompany'])->name('masters.companies.toggle');

        Route::post('/masters/ptw', [DailyWorkController::class, 'storePtw'])->name('masters.ptw.store');
        Route::put('/masters/ptw/{id}', [DailyWorkController::class, 'updatePtw'])->name('masters.ptw.update');
        Route::delete('/masters/ptw/{id}', [DailyWorkController::class, 'destroyPtw'])->name('masters.ptw.destroy');

        Route::post('/masters/sikka', [DailyWorkController::class, 'storeSikka'])->name('masters.sikka.store');
        Route::put('/masters/sikka/{id}', [DailyWorkController::class, 'updateSikka'])->name('masters.sikka.update');
        Route::delete('/masters/sikka/{id}', [DailyWorkController::class, 'destroySikka'])->name('masters.sikka.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| HSSE Plan
|--------------------------------------------------------------------------
*/

Route::prefix('hsse-plan')->name('hsse-plan.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [HSSEPlanController::class, 'index'])->name('index');
    Route::get('/check-duplicate', [HSSEPlanController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::get('/riwayat-tercapai', [HSSEPlanController::class, 'achieved'])->name('achieved');
    Route::get('/achieved', [HSSEPlanController::class, 'achieved'])->name('riwayat-tercapai');
    Route::get('/export', [HSSEPlanController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/{kpi}', [HSSEPlanController::class, 'show'])->name('show');
    Route::get('/{kpi}/notes', [HSSEPlanController::class, 'getNotes'])->name('notes.index');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/', [HSSEPlanController::class, 'store'])->name('store');
        Route::put('/{kpi}', [HSSEPlanController::class, 'update'])->name('update');
        Route::delete('/{kpi}', [HSSEPlanController::class, 'destroy'])->name('destroy');

        // Catatan (Notes) CRUD
        Route::post('/{kpi}/notes', [HSSEPlanController::class, 'storeNote'])->name('notes.store');
        Route::put('/{kpi}/notes/{note}', [HSSEPlanController::class, 'updateNote'])->name('notes.update');
        Route::delete('/{kpi}/notes/{note}', [HSSEPlanController::class, 'destroyNote'])->name('notes.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

Route::prefix('events')->name('events.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/check-duplicate', [EventController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::get('/export', [EventController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
        Route::post('/{event}/upload', [EventController::class, 'uploadDocumentation'])->name('upload');
    });
});

/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/

Route::prefix('inventory')->name('inventory.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role: Admin Management, Admin Inventory, User)
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/export', [InventoryController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/transactions', [InventoryController::class, 'transactions'])->name('transactions');

    // Write actions (Admin Management & Admin Inventory, Ditolak untuk User)
    Route::middleware(['can.manage:inventory'])->group(function () {
        Route::post('/', [InventoryController::class, 'storeItem'])->name('store');
        Route::put('/{inventory}', [InventoryController::class, 'updateItem'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroyItem'])->name('destroy');
        Route::post('/transactions', [InventoryController::class, 'storeTransaction'])->name('transactions.store');
        Route::post('/borrowings', [InventoryController::class, 'storeBorrowing'])->name('borrowings.store');
        Route::post('/borrowings/{borrowing}/return', [InventoryController::class, 'returnBorrowing'])->name('borrowings.return');
    });
});

/*
|--------------------------------------------------------------------------
| Gas Detector / Gas Monitoring
|--------------------------------------------------------------------------
*/

Route::prefix('gas-detector')->name('gas-detector.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [GasDetectorController::class, 'index'])->name('index');
    Route::get('/check-duplicate', [GasDetectorController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::get('/export', [GasDetectorController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/measurements/export', [GasDetectorController::class, 'exportMeasurements'])->name('measurements.export')->middleware('can.export');
    Route::get('/measurements/{gasMonitoring}', [GasDetectorController::class, 'showMeasurement'])->name('measurements.show');
    Route::get('/{gas_detector}', [GasDetectorController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/measurements/batch', [GasDetectorController::class, 'storeBatchMeasurements'])->name('measurements.store-batch');
        Route::post('/measurements', [GasDetectorController::class, 'storeMeasurement'])->name('measurements.store');
        Route::put('/measurements/{gasMonitoring}', [GasDetectorController::class, 'updateMeasurement'])->name('measurements.update');
        Route::delete('/measurements/{gasMonitoring}', [GasDetectorController::class, 'destroyMeasurement'])->name('measurements.destroy');

        // Well Pad CRUD
        Route::get('/well-pads/data', [GasDetectorController::class, 'getWellPadsData'])->name('well-pads.data');
        Route::post('/well-pads', [GasDetectorController::class, 'storeWellPad'])->name('well-pads.store');
        Route::put('/well-pads/{wellPad}', [GasDetectorController::class, 'updateWellPad'])->name('well-pads.update');
        Route::delete('/well-pads/{wellPad}', [GasDetectorController::class, 'destroyWellPad'])->name('well-pads.destroy');

        Route::post('/', [GasDetectorController::class, 'store'])->name('store');
        Route::put('/{gas_detector}', [GasDetectorController::class, 'update'])->name('update');
        Route::delete('/{gas_detector}', [GasDetectorController::class, 'destroy'])->name('destroy');
    });
});

// Alias routes untuk gas-monitoring
Route::prefix('gas-monitoring')->name('gas-monitoring.')->middleware(['auth'])->group(function () {
    Route::get('/', [GasDetectorController::class, 'index'])->name('index');
    Route::get('/export', [GasDetectorController::class, 'exportMeasurements'])->name('export')->middleware('can.export');

    Route::middleware(['admin'])->group(function () {
        Route::post('/batch', [GasDetectorController::class, 'storeBatchMeasurements'])->name('store-batch');
        Route::post('/', [GasDetectorController::class, 'storeMeasurement'])->name('store');
        Route::put('/{gasMonitoring}', [GasDetectorController::class, 'updateMeasurement'])->name('update');
        Route::delete('/{gasMonitoring}', [GasDetectorController::class, 'destroyMeasurement'])->name('destroy');
    });
});

Route::get('/gasdetektor', function () {
    return redirect()->route('gas-detector.index');
})->middleware(['auth']);

/*
|--------------------------------------------------------------------------
| Workforce / Manpower
|--------------------------------------------------------------------------
*/

Route::prefix('workforce')->name('workforce.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [WorkforceController::class, 'index'])->name('index');
    Route::get('/check-duplicate', [WorkforceController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::get('/by-date', [WorkforceController::class, 'getByDate'])->name('by-date');
    Route::get('/export', [WorkforceController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/{workforce}', [WorkforceController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/batch', [WorkforceController::class, 'storeBatch'])->name('store-batch');
        Route::post('/units', [WorkforceController::class, 'storeUnit'])->name('units.store');
        Route::put('/units/{unit}', [WorkforceController::class, 'updateUnit'])->name('units.update');
        Route::patch('/units/{unit}/toggle', [WorkforceController::class, 'toggleUnit'])->name('units.toggle');
        Route::post('/', [WorkforceController::class, 'store'])->name('store');
        Route::put('/{workforce}', [WorkforceController::class, 'update'])->name('update');
        Route::delete('/{workforce}', [WorkforceController::class, 'destroy'])->name('destroy');
        Route::post('/destroy-bulk', [WorkforceController::class, 'destroyBulk'])->name('destroy-bulk');
    });
});

/*
|--------------------------------------------------------------------------
| Man Hours
|--------------------------------------------------------------------------
*/

Route::prefix('man-hours')->name('man-hours.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [ManHoursController::class, 'index'])->name('index');
    Route::get('/export', [ManHoursController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/export-excel', [ManHoursController::class, 'exportExcel'])->name('export-excel')->middleware('can.export');
    Route::get('/by-date', [WorkforceController::class, 'getByDate'])->name('by-date');
    Route::get('/{man_hour}', [ManHoursController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/batch', [WorkforceController::class, 'storeBatch'])->name('store-batch');
        Route::post('/units', [WorkforceController::class, 'storeUnit'])->name('units.store');
        Route::put('/units/{unit}', [WorkforceController::class, 'updateUnit'])->name('units.update');
        Route::patch('/units/{unit}/toggle', [WorkforceController::class, 'toggleUnit'])->name('units.toggle');
        Route::put('/units/{unit}/accumulated-hours', [ManHoursController::class, 'updateUnitAccumulatedHours'])->name('units.accumulated-hours');
        Route::post('/units/{unit}/accumulated-hours', [ManHoursController::class, 'updateUnitAccumulatedHours'])->name('units.accumulated-hours.post');
        Route::put('/{man_hour}', [ManHoursController::class, 'update'])->name('update');
        Route::delete('/date/{date}', [ManHoursController::class, 'destroyDate'])->name('destroy-date');
    });
});

/*
|--------------------------------------------------------------------------
| Incident
|--------------------------------------------------------------------------
*/

Route::prefix('incident')->name('incident.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [IncidentController::class, 'index'])->name('index');
    Route::get('/export', [IncidentController::class, 'export'])->name('export')->middleware('can.export');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::post('/', [IncidentController::class, 'store'])->name('store');
        Route::put('/{incident}', [IncidentController::class, 'update'])->name('update');
        Route::delete('/{incident}', [IncidentController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Issue & Follow Up
|--------------------------------------------------------------------------
*/

Route::prefix('issue-follow-up')->name('issue-follow-up.')->middleware(['auth'])->group(function () {
    // Read & Export (Semua Role)
    Route::get('/', [IssueFollowUpController::class, 'index'])->name('index');
    Route::get('/export', [IssueFollowUpController::class, 'export'])->name('export')->middleware('can.export');
    Route::get('/{issue_follow_up}', [IssueFollowUpController::class, 'show'])->name('show');

    // Write actions (Hanya Admin Management)
    Route::middleware(['admin'])->group(function () {
        Route::get('/create', [IssueFollowUpController::class, 'create'])->name('create');
        Route::post('/', [IssueFollowUpController::class, 'store'])->name('store');
        Route::get('/{issue_follow_up}/edit', [IssueFollowUpController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{issue_follow_up}', [IssueFollowUpController::class, 'update'])->name('update');
        Route::delete('/{issue_follow_up}', [IssueFollowUpController::class, 'destroy'])->name('destroy');

        Route::post('/{issue_follow_up}/follow-up', [IssueFollowUpController::class, 'addFollowUp'])->name('follow-up');
        Route::match(['post', 'patch'], '/{issue_follow_up}/close', [IssueFollowUpController::class, 'close'])->name('close');
        Route::match(['post', 'patch'], '/{issue_follow_up}/reopen', [IssueFollowUpController::class, 'reopen'])->name('reopen');
    });
});

/*
|--------------------------------------------------------------------------
| User Management (Manajemen Pengguna - Hanya Admin Management)
|--------------------------------------------------------------------------
*/

Route::prefix('users')->name('users.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/activities/all', [UserController::class, 'allActivities'])->name('activities.all');
    Route::get('/{user}/activities', [UserController::class, 'activities'])->name('activities');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
});
