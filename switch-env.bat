@echo off
setlocal enabledelayedexpansion

title Paddle Field Sports Center - Environment Switcher (DEV / PROD)

:: Navigate to project root
cd /d "%~dp0"

:: 1. Auto-detect PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;!PATH!"
    ) else if exist "D:\xampp\php\php.exe" (
        set "PATH=D:\xampp\php;!PATH!"
    )
)

:MENU
cls
echo ===============================================================================
echo            PADDLE FIELD SPORTS - ENVIRONMENT SWITCHER (DEV / PROD)
echo ===============================================================================
echo.

:: Detect current mode from .env
set "CURRENT_ENV=unknown"
set "CURRENT_DB=unknown"
set "CURRENT_DEBUG=unknown"

if exist ".env" (
    for /f "tokens=1,2 delims==" %%a in ('findstr /r "^APP_ENV=" .env 2^>nul') do set "CURRENT_ENV=%%b"
    for /f "tokens=1,2 delims==" %%a in ('findstr /r "^DB_CONNECTION=" .env 2^>nul') do set "CURRENT_DB=%%b"
    for /f "tokens=1,2 delims==" %%a in ('findstr /r "^APP_DEBUG=" .env 2^>nul') do set "CURRENT_DEBUG=%%b"
)

echo  CURRENT STATUS:
if /i "!CURRENT_ENV!"=="production" (
    echo   [!] Active Mode    : PROD (Production / Laravel Cloud)
    echo   [!] Database Driver: !CURRENT_DB! (MySQL)
    echo   [!] Debug Mode     : !CURRENT_DEBUG! (Disabled)
) else (
    echo   [*] Active Mode    : DEV (Local Development)
    echo   [*] Database Driver: !CURRENT_DB! (SQLite)
    echo   [*] Debug Mode     : !CURRENT_DEBUG! (Enabled)
)
echo.
echo -------------------------------------------------------------------------------
echo  CHOOSE AN OPTION:
echo -------------------------------------------------------------------------------
echo   [1] Switch to DEV   - Local SQLite database, debug ON, verbose logs
echo   [2] Switch to PROD  - MySQL database (Laravel Cloud), debug OFF, prod security
echo   [3] Toggle Switch   - Instant flip between DEV ^<--^> PROD
echo   [4] Run Migrations  - Migrate current active database (php artisan migrate)
echo   [5] Seed Database   - Run seeders on current database (php artisan db:seed)
echo   [6] Exit
echo -------------------------------------------------------------------------------
echo.

set /p "CHOICE=Enter your choice [1-6]: "

if "%CHOICE%"=="1" goto TO_DEV
if "%CHOICE%"=="2" goto TO_PROD
if "%CHOICE%"=="3" goto TOGGLE
if "%CHOICE%"=="4" goto MIGRATE
if "%CHOICE%"=="5" goto SEED
if "%CHOICE%"=="6" goto EXIT
goto MENU

:TO_DEV
echo.
echo [*] Switching to DEV (SQLite)...
php artisan app:switch-env dev
echo.
pause
goto MENU

:TO_PROD
echo.
echo [*] Switching to PROD (MySQL)...
php artisan app:switch-env prod
echo.
pause
goto MENU

:TOGGLE
echo.
echo [*] Toggling environment...
php artisan app:switch-env
echo.
pause
goto MENU

:MIGRATE
echo.
echo [*] Running migrations on current database (!CURRENT_DB!)...
php artisan migrate
echo.
pause
goto MENU

:SEED
echo.
echo [*] Running DatabaseSeeder on current database (!CURRENT_DB!)...
php artisan db:seed --force
echo.
pause
goto MENU

:EXIT
exit /b 0
