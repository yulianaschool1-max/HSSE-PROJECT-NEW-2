@echo off
title Perbaikan Struktur HSSE
cd /d "%~dp0"

echo ==========================================
echo     PERBAIKAN STRUKTUR PROYEK HSSE
echo ==========================================
echo.

REM MODELS
call :makefile "app\Models\Event.php"
call :makefile "app\Models\InventoryItem.php"
call :makefile "app\Models\InventoryTransaction.php"
call :makefile "app\Models\Workforce.php"
call :makefile "app\Models\ManHour.php"
call :makefile "app\Models\Incident.php"
call :makefile "app\Models\IssueFollowUp.php"
call :makefile "app\Models\Document.php"
call :makefile "app\Models\ActivityLog.php"

REM SERVICES
call :makefile "app\Services\Reports\HSSEReportService.php"

REM MIGRATIONS - KERANGKA KOSONG
call :makefile "database\migrations\2026_10_10_000001_create_daily_works_table.php"
call :makefile "database\migrations\2026_10_10_000002_create_hsse_plans_table.php"
call :makefile "database\migrations\2026_10_10_000003_create_events_table.php"
call :makefile "database\migrations\2026_10_10_000004_create_inventory_items_table.php"
call :makefile "database\migrations\2026_10_10_000005_create_inventory_transactions_table.php"
call :makefile "database\migrations\2026_10_10_000006_create_workforces_table.php"
call :makefile "database\migrations\2026_10_10_000007_create_man_hours_table.php"
call :makefile "database\migrations\2026_10_10_000008_create_incidents_table.php"
call :makefile "database\migrations\2026_10_10_000009_create_issue_follow_ups_table.php"
call :makefile "database\migrations\2026_10_10_000010_create_documents_table.php"
call :makefile "database\migrations\2026_10_10_000011_create_activity_logs_table.php"

REM FACTORIES
call :makefile "database\factories\DailyWorkFactory.php"
call :makefile "database\factories\HSSEPlanFactory.php"
call :makefile "database\factories\EventFactory.php"
call :makefile "database\factories\InventoryItemFactory.php"
call :makefile "database\factories\WorkforceFactory.php"
call :makefile "database\factories\ManHourFactory.php"
call :makefile "database\factories\IncidentFactory.php"
call :makefile "database\factories\IssueFollowUpFactory.php"

REM SEEDERS
call :makefile "database\seeders\HSSESeeder.php"
call :makefile "database\seeders\DailyWorkSeeder.php"
call :makefile "database\seeders\InventorySeeder.php"
call :makefile "database\seeders\WorkforceSeeder.php"
call :makefile "database\seeders\IncidentSeeder.php"

REM FORM REQUESTS
call :makefile "app\Http\Requests\DailyWork\StoreDailyWorkRequest.php"
call :makefile "app\Http\Requests\DailyWork\UpdateDailyWorkRequest.php"
call :makefile "app\Http\Requests\HSSEPlan\StoreHSSEPlanRequest.php"
call :makefile "app\Http\Requests\HSSEPlan\UpdateHSSEPlanRequest.php"
call :makefile "app\Http\Requests\Events\StoreEventRequest.php"
call :makefile "app\Http\Requests\Events\UpdateEventRequest.php"
call :makefile "app\Http\Requests\Inventory\StoreInventoryItemRequest.php"
call :makefile "app\Http\Requests\Inventory\UpdateInventoryItemRequest.php"
call :makefile "app\Http\Requests\Workforce\StoreWorkforceRequest.php"
call :makefile "app\Http\Requests\ManHours\StoreManHourRequest.php"
call :makefile "app\Http\Requests\Incident\StoreIncidentRequest.php"
call :makefile "app\Http\Requests\IssueFollowUp\StoreIssueFollowUpRequest.php"
call :makefile "app\Http\Requests\Document\StoreDocumentRequest.php"

echo.
echo ==========================================
echo Struktur tambahan selesai diperiksa.
echo File lama tidak ditimpa.
echo ==========================================
echo.
pause
exit /b

:makefile
if not exist "%~1" (
    for %%D in ("%~1") do if not exist "%%~dpD" mkdir "%%~dpD"
    type nul > "%~1"
    echo [DIBUAT] %~1
) else (
    echo [SUDAH ADA] %~1
)
exit /b

