
@echo off
cd /d "C:\laragon\www\HSSE PROJECT"

REM ===== FOLDER BACKEND =====
mkdir app\Http\Controllers\Modules 2>nul
mkdir app\Services 2>nul
mkdir app\Models 2>nul

REM ===== FOLDER VIEWS =====
mkdir resources\views\layouts 2>nul
mkdir resources\views\components 2>nul
mkdir resources\views\auth 2>nul
mkdir resources\views\dashboard 2>nul
mkdir resources\views\daily-work 2>nul
mkdir resources\views\hsse-plan 2>nul
mkdir resources\views\events 2>nul
mkdir resources\views\inventory 2>nul
mkdir resources\views\data\man-hours 2>nul
mkdir resources\views\data\workforce 2>nul
mkdir resources\views\data\incident 2>nul
mkdir resources\views\data\issue-follow-up 2>nul
mkdir resources\views\users 2>nul

REM ===== FOLDER CSS =====
mkdir public\css\layouts 2>nul
mkdir public\css\auth 2>nul
mkdir public\css\dashboard 2>nul
mkdir public\css\daily-work 2>nul
mkdir public\css\hsse-plan 2>nul
mkdir public\css\events 2>nul
mkdir public\css\inventory 2>nul
mkdir public\css\data\man-hours 2>nul
mkdir public\css\data\workforce 2>nul
mkdir public\css\data\incident 2>nul
mkdir public\css\data\issue-follow-up 2>nul
mkdir public\css\users 2>nul

REM ===== FOLDER JAVASCRIPT =====
mkdir public\js\layouts 2>nul
mkdir public\js\dashboard 2>nul
mkdir public\js\daily-work 2>nul
mkdir public\js\hsse-plan 2>nul
mkdir public\js\events 2>nul
mkdir public\js\inventory 2>nul
mkdir public\js\data\man-hours 2>nul
mkdir public\js\data\workforce 2>nul
mkdir public\js\data\incident 2>nul
mkdir public\js\data\issue-follow-up 2>nul
mkdir public\js\users 2>nul

REM ===== FOLDER DOKUMENTASI =====
mkdir storage\app\private\documents\daily-work 2>nul
mkdir storage\app\private\documents\hsse-plan 2>nul
mkdir storage\app\private\documents\events 2>nul
mkdir storage\app\private\documents\inventory 2>nul
mkdir storage\app\private\documents\workforce 2>nul
mkdir storage\app\private\documents\incident 2>nul
mkdir storage\app\private\documents\issue-follow-up 2>nul
mkdir storage\app\private\documents\other 2>nul

REM ===== FOLDER GAMBAR =====
mkdir public\images\logo 2>nul
mkdir public\images\backgrounds 2>nul
mkdir public\images\icons 2>nul

REM ===== FILE LAYOUT =====
call :file resources\views\layouts\app.blade.php
call :file resources\views\layouts\auth.blade.php

REM ===== FILE COMPONENTS =====
call :file resources\views\components\sidebar.blade.php
call :file resources\views\components\navbar.blade.php
call :file resources\views\components\footer.blade.php
call :file resources\views\components\stat-card.blade.php
call :file resources\views\components\chart-card.blade.php
call :file resources\views\components\document-upload.blade.php
call :file resources\views\components\document-list.blade.php

REM ===== FILE AUTH =====
call :file resources\views\auth\login.blade.php
call :file resources\views\auth\register.blade.php

REM ===== FILE DASHBOARD =====
call :file resources\views\dashboard\index.blade.php

REM ===== FILE MODUL UTAMA =====
for %%M in (daily-work hsse-plan events inventory) do (
    call :file resources\views\%%M\index.blade.php
    call :file resources\views\%%M\create.blade.php
    call :file resources\views\%%M\edit.blade.php
    call :file resources\views\%%M\show.blade.php
)

call :file resources\views\inventory\transactions.blade.php

REM ===== FILE DATA =====
for %%M in (workforce incident issue-follow-up) do (
    call :file resources\views\data\%%M\index.blade.php
    call :file resources\views\data\%%M\create.blade.php
    call :file resources\views\data\%%M\edit.blade.php
    call :file resources\views\data\%%M\show.blade.php
)

call :file resources\views\data\man-hours\index.blade.php

REM ===== FILE USERS =====
call :file resources\views\users\index.blade.php
call :file resources\views\users\create.blade.php
call :file resources\views\users\edit.blade.php

REM ===== FILE CSS =====
call :file public\css\layouts\app.css
call :file public\css\layouts\sidebar.css
call :file public\css\layouts\navbar.css
call :file public\css\auth\login.css
call :file public\css\auth\register.css
call :file public\css\dashboard\index.css

for %%M in (daily-work hsse-plan events inventory) do (
    call :file public\css\%%M\index.css
    call :file public\css\%%M\form.css
)

for %%M in (man-hours workforce incident issue-follow-up) do (
    call :file public\css\data\%%M\index.css
    call :file public\css\data\%%M\form.css
)

call :file public\css\users\index.css
call :file public\css\users\form.css

REM ===== FILE JAVASCRIPT =====
call :file public\js\layouts\app.js
call :file public\js\dashboard\index.js

for %%M in (daily-work hsse-plan events inventory) do (
    call :file public\js\%%M\index.js
)

for %%M in (man-hours workforce incident issue-follow-up) do (
    call :file public\js\data\%%M\index.js
)

call :file public\js\users\index.js

REM ===== FILE CONTROLLER =====
call :file app\Http\Controllers\DashboardController.php
call :file app\Http\Controllers\Modules\DailyWorkController.php
call :file app\Http\Controllers\Modules\HSSEPlanController.php
call :file app\Http\Controllers\Modules\EventController.php
call :file app\Http\Controllers\Modules\InventoryController.php
call :file app\Http\Controllers\Modules\ManHoursController.php
call :file app\Http\Controllers\Modules\WorkforceController.php
call :file app\Http\Controllers\Modules\IncidentController.php
call :file app\Http\Controllers\Modules\IssueFollowUpController.php
call :file app\Http\Controllers\Modules\UserController.php
call :file app\Http\Controllers\Modules\DocumentController.php

REM ===== FILE SERVICE =====
call :file app\Services\DashboardService.php
call :file app\Services\ManHoursService.php
call :file app\Services\InventoryService.php
call :file app\Services\IssueFollowUpService.php

REM ===== SELESAI =====
echo.
echo Semua folder dan file kosong selesai dibuat.
echo File lama tidak ditimpa.
pause
exit /b

:file
if not exist "%~1" type nul > "%~1"
exit /b
