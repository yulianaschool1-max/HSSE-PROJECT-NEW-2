@echo off
setlocal
cd /d "C:\laragon\www\HSSE PROJECT"

echo ==========================================
echo   MELENGKAPI KERANGKA PROYEK HSSE
echo   HANYA MEMBUAT FILE KOSONG
echo ==========================================
echo.

REM ==========================================
REM 1. FOLDER BACKEND TAMBAHAN
REM ==========================================
mkdir app\Http\Requests 2>nul
mkdir app\Http\Requests\Auth 2>nul
mkdir app\Http\Requests\DailyWork 2>nul
mkdir app\Http\Requests\HSSEPlan 2>nul
mkdir app\Http\Requests\Events 2>nul
mkdir app\Http\Requests\Inventory 2>nul
mkdir app\Http\Requests\Workforce 2>nul
mkdir app\Http\Requests\ManHours 2>nul
mkdir app\Http\Requests\Incident 2>nul
mkdir app\Http\Requests\IssueFollowUp 2>nul
mkdir app\Http\Requests\User 2>nul
mkdir app\Http\Requests\Document 2>nul

mkdir app\Policies 2>nul
mkdir app\Rules 2>nul
mkdir app\Enums 2>nul
mkdir app\Helpers 2>nul
mkdir app\Notifications 2>nul
mkdir app\Jobs 2>nul
mkdir app\Events 2>nul
mkdir app\Listeners 2>nul

mkdir app\Exports 2>nul
mkdir app\Imports 2>nul
mkdir app\Services\Reports 2>nul

REM ==========================================
REM 2. FOLDER DATABASE
REM ==========================================
mkdir database\factories 2>nul
mkdir database\seeders 2>nul
mkdir database\migrations 2>nul

REM ==========================================
REM 3. FOLDER TESTING
REM ==========================================
mkdir tests\Feature\Auth 2>nul
mkdir tests\Feature\Dashboard 2>nul
mkdir tests\Feature\DailyWork 2>nul
mkdir tests\Feature\HSSEPlan 2>nul
mkdir tests\Feature\Events 2>nul
mkdir tests\Feature\Inventory 2>nul
mkdir tests\Feature\Workforce 2>nul
mkdir tests\Feature\ManHours 2>nul
mkdir tests\Feature\Incident 2>nul
mkdir tests\Feature\IssueFollowUp 2>nul
mkdir tests\Feature\Users 2>nul
mkdir tests\Feature\Documents 2>nul
mkdir tests\Unit\Services 2>nul
mkdir tests\Unit\Models 2>nul

REM ==========================================
REM 4. FOLDER VIEWS TAMBAHAN
REM ==========================================
mkdir resources\views\errors 2>nul
mkdir resources\views\partials 2>nul
mkdir resources\views\reports 2>nul
mkdir resources\views\exports 2>nul
mkdir resources\views\profile 2>nul
mkdir resources\views\settings 2>nul

REM ==========================================
REM 5. FOLDER CSS TAMBAHAN
REM ==========================================
mkdir public\css\errors 2>nul
mkdir public\css\reports 2>nul
mkdir public\css\profile 2>nul
mkdir public\css\settings 2>nul

REM ==========================================
REM 6. FOLDER JAVASCRIPT TAMBAHAN
REM ==========================================
mkdir public\js\reports 2>nul
mkdir public\js\profile 2>nul
mkdir public\js\settings 2>nul

REM ==========================================
REM 7. FOLDER EXPORT DAN TEMPORARY
REM ==========================================
mkdir storage\app\private\exports 2>nul
mkdir storage\app\private\imports 2>nul
mkdir storage\app\private\reports 2>nul
mkdir storage\app\private\temp 2>nul

REM ==========================================
REM 8. FOLDER DOKUMENTASI PROYEK
REM ==========================================
mkdir docs 2>nul
mkdir docs\database 2>nul
mkdir docs\api 2>nul
mkdir docs\user-guide 2>nul
mkdir docs\deployment 2>nul

REM ==========================================
REM 9. MODEL DATABASE
REM User.php tidak dibuat ulang karena bawaan Laravel.
REM ==========================================
call :file app\Models\DailyWork.php
call :file app\Models\HSSEPlan.php
call :file app\Models\Event.php
call :file app\Models\InventoryItem.php
call :file app\Models\InventoryTransaction.php
call :file app\Models\Workforce.php
call :file app\Models\ManHour.php
call :file app\Models\Incident.php
call :file app\Models\IssueFollowUp.php
call :file app\Models\Document.php
call :file app\Models\ActivityLog.php

REM ==========================================
REM 10. MIGRATION DATABASE
REM File kosong, belum siap dijalankan.
REM ==========================================
call :file database\migrations\2026_10_10_000001_create_workforces_table.php
call :file database\migrations\2026_10_10_000002_create_daily_works_table.php
call :file database\migrations\2026_10_10_000003_create_hsse_plans_table.php
call :file database\migrations\2026_10_10_000004_create_events_table.php
call :file database\migrations\2026_10_10_000005_create_inventory_items_table.php
call :file database\migrations\2026_10_10_000006_create_inventory_transactions_table.php
call :file database\migrations\2026_10_10_000007_create_man_hours_table.php
call :file database\migrations\2026_10_10_000008_create_incidents_table.php
call :file database\migrations\2026_10_10_000009_create_issue_follow_ups_table.php
call :file database\migrations\2026_10_10_000010_create_documents_table.php
call :file database\migrations\2026_10_10_000011_create_activity_logs_table.php

REM ==========================================
REM 11. FORM REQUEST / VALIDASI
REM ==========================================
call :file app\Http\Requests\Auth\LoginRequest.php
call :file app\Http\Requests\DailyWork\StoreDailyWorkRequest.php
call :file app\Http\Requests\DailyWork\UpdateDailyWorkRequest.php
call :file app\Http\Requests\HSSEPlan\StoreHSSEPlanRequest.php
call :file app\Http\Requests\HSSEPlan\UpdateHSSEPlanRequest.php
call :file app\Http\Requests\Events\StoreEventRequest.php
call :file app\Http\Requests\Events\UpdateEventRequest.php
call :file app\Http\Requests\Inventory\StoreInventoryItemRequest.php
call :file app\Http\Requests\Inventory\UpdateInventoryItemRequest.php
call :file app\Http\Requests\Inventory\StoreInventoryTransactionRequest.php
call :file app\Http\Requests\Workforce\StoreWorkforceRequest.php
call :file app\Http\Requests\Workforce\UpdateWorkforceRequest.php
call :file app\Http\Requests\ManHours\StoreManHourRequest.php
call :file app\Http\Requests\Incident\StoreIncidentRequest.php
call :file app\Http\Requests\Incident\UpdateIncidentRequest.php
call :file app\Http\Requests\IssueFollowUp\StoreIssueFollowUpRequest.php
call :file app\Http\Requests\IssueFollowUp\UpdateIssueFollowUpRequest.php
call :file app\Http\Requests\User\StoreUserRequest.php
call :file app\Http\Requests\User\UpdateUserRequest.php
call :file app\Http\Requests\Document\StoreDocumentRequest.php

REM ==========================================
REM 12. POLICY DAN MIDDLEWARE
REM ==========================================
call :file app\Policies\DailyWorkPolicy.php
call :file app\Policies\HSSEPlanPolicy.php
call :file app\Policies\EventPolicy.php
call :file app\Policies\InventoryItemPolicy.php
call :file app\Policies\IncidentPolicy.php
call :file app\Policies\IssueFollowUpPolicy.php
call :file app\Policies\UserPolicy.php
call :file app\Http\Middleware\EnsureUserHasRole.php

REM ==========================================
REM 13. EXPORT, IMPORT, DAN REPORT
REM ==========================================
call :file app\Exports\DailyWorkExport.php
call :file app\Exports\InventoryExport.php
call :file app\Exports\WorkforceExport.php
call :file app\Exports\ManHoursExport.php
call :file app\Exports\IncidentExport.php
call :file app\Exports\IssueFollowUpExport.php
call :file app\Imports\WorkforceImport.php
call :file app\Imports\InventoryImport.php
call :file app\Services\Reports\HSSEReportService.php

REM ==========================================
REM 14. CONTROLLER TAMBAHAN
REM ==========================================
call :file app\Http\Controllers\Modules\ReportController.php
call :file app\Http\Controllers\Modules\ProfileController.php
call :file app\Http\Controllers\Modules\SettingController.php

REM ==========================================
REM 15. FACTORY DAN SEEDER
REM ==========================================
call :file database\factories\DailyWorkFactory.php
call :file database\factories\HSSEPlanFactory.php
call :file database\factories\EventFactory.php
call :file database\factories\InventoryItemFactory.php
call :file database\factories\WorkforceFactory.php
call :file database\factories\IncidentFactory.php
call :file database\factories\IssueFollowUpFactory.php

call :file database\seeders\RoleSeeder.php
call :file database\seeders\HSSESeeder.php

REM ==========================================
REM 16. HALAMAN TAMBAHAN
REM ==========================================
call :file resources\views\errors\403.blade.php
call :file resources\views\errors\404.blade.php
call :file resources\views\errors\419.blade.php
call :file resources\views\errors\500.blade.php
call :file resources\views\reports\index.blade.php
call :file resources\views\profile\index.blade.php
call :file resources\views\settings\index.blade.php

REM ==========================================
REM 17. CSS TAMBAHAN
REM ==========================================
call :file public\css\inventory\transactions.css
call :file public\css\data\man-hours\form.css
call :file public\css\reports\index.css
call :file public\css\profile\index.css
call :file public\css\settings\index.css
call :file public\css\errors\error.css

REM ==========================================
REM 18. JAVASCRIPT TAMBAHAN
REM ==========================================
call :file public\js\inventory\transactions.js
call :file public\js\data\man-hours\form.js
call :file public\js\reports\index.js
call :file public\js\profile\index.js
call :file public\js\settings\index.js

REM ==========================================
REM 19. TESTING
REM ==========================================
call :file tests\Feature\Auth\LoginTest.php
call :file tests\Feature\Dashboard\DashboardTest.php
call :file tests\Feature\DailyWork\DailyWorkTest.php
call :file tests\Feature\HSSEPlan\HSSEPlanTest.php
call :file tests\Feature\Events\EventTest.php
call :file tests\Feature\Inventory\InventoryTest.php
call :file tests\Feature\Workforce\WorkforceTest.php
call :file tests\Feature\ManHours\ManHoursTest.php
call :file tests\Feature\Incident\IncidentTest.php
call :file tests\Feature\IssueFollowUp\IssueFollowUpTest.php
call :file tests\Feature\Users\UserTest.php
call :file tests\Feature\Documents\DocumentTest.php
call :file tests\Unit\Services\ManHoursServiceTest.php
call :file tests\Unit\Services\InventoryServiceTest.php
call :file tests\Unit\Services\IssueFollowUpServiceTest.php

REM ==========================================
REM 20. DOKUMENTASI
REM ==========================================
call :file docs\README.md
call :file docs\database\database-design.md
call :file docs\database\relationships.md
call :file docs\api\endpoints.md
call :file docs\user-guide\user-guide.md
call :file docs\deployment\deployment-guide.md

echo.
echo ==========================================
echo Kerangka tambahan selesai dibuat.
echo File yang sudah ada tidak ditimpa.
echo ==========================================
echo.
echo CATATAN:
echo Semua file yang baru dibuat masih kosong.
echo Jangan jalankan migrate sebelum migration diisi.
echo Periksa file sebelum mengisi kode.
echo.
pause
exit /b

:file
if not exist "%~1" type nul > "%~1"
exit /b

