@echo off
setlocal enabledelayedexpansion

title Paddle Field Sports Center - Development Environment

:: Navigate to application root
cd /d "%~dp0"

echo ===============================================================================
echo            PADDLE FIELD SPORTS CENTER - DEVELOPMENT LAUNCHER
echo ===============================================================================
echo.

:: 1. Auto-detect PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;!PATH!"
        echo [*] Auto-detected PHP at C:\xampp\php
    ) else if exist "D:\xampp\php\php.exe" (
        set "PATH=D:\xampp\php;!PATH!"
        echo [*] Auto-detected PHP at D:\xampp\php
    ) else (
        echo [ERROR] PHP executable was not found in PATH or standard XAMPP paths.
        echo Please ensure PHP is installed and configured in your system environment.
        echo.
        pause
        exit /b 1
    )
)

:: 2. Auto-detect NPM / Node for Vite
set "HAS_NPM=0"
where npm >nul 2>&1
if %errorlevel% equ 0 (
    if exist "%~dp0node_modules" (
        set "HAS_NPM=1"
    )
)

:START_SERVERS
echo [*] Cleaning up any previous server instances on ports 8000, 8085, 5173...
call "%~dp0stop-dev.bat" >nul 2>&1
ping -n 2 127.0.0.1 >nul

echo.
echo [1/3] Starting Laravel Web Server on http://127.0.0.1:8000...
start "PaddleField - Laravel Server [Port 8000]" cmd /k "title PaddleField - Laravel Server [Port 8000] && color 0B && cd /d ""%~dp0"" && echo =================================================== && echo   PADDLE FIELD - LARAVEL HTTP SERVER [PORT 8000] && echo   URL: http://127.0.0.1:8000 && echo =================================================== && echo. && php artisan serve --host=127.0.0.1 --port=8000"

ping -n 2 127.0.0.1 >nul

echo [2/3] Starting Laravel Reverb WebSocket Server on ws://127.0.0.1:8085...
start "PaddleField - Reverb WebSockets [Port 8085]" cmd /k "title PaddleField - Reverb WebSockets [Port 8085] && color 0A && cd /d ""%~dp0"" && echo =================================================== && echo   PADDLE FIELD - LARAVEL REVERB WEBSOCKETS && echo   Host: ws://127.0.0.1:8085 [Debug Mode Active] && echo =================================================== && echo. && php artisan reverb:start --host=0.0.0.0 --port=8085 --debug"

if !HAS_NPM! equ 1 (
    ping -n 2 127.0.0.1 >nul
    echo [3/3] Starting Vite Frontend Asset Server [HMR]...
    start "PaddleField - Vite Dev" cmd /k "title PaddleField - Vite Dev && color 0E && cd /d ""%~dp0"" && echo =================================================== && echo   PADDLE FIELD - VITE DEV / ASSET HOT RELOAD && echo =================================================== && echo. && npm run dev"
) else (
    echo [3/3] Vite dev server skipped [npm or node_modules not detected].
)

echo.
echo [OK] All development services have been launched!
ping -n 3 127.0.0.1 >nul

:MENU
cls
echo ===============================================================================
echo                PADDLE FIELD SPORTS CENTER - DEV CONTROL PANEL
echo ===============================================================================
echo.
echo   [ACTIVE SERVICES]
echo     * Laravel HTTP Server : http://127.0.0.1:8000
echo     * Reverb WebSockets   : ws://127.0.0.1:8085 [Port 8085]
if !HAS_NPM! equ 1 (
echo     * Vite HMR Dev Server : http://localhost:5173
)
echo.
echo   [QUICK ACTIONS]
echo     [O] Open Web App in Default Browser (http://127.0.0.1:8000)
echo     [A] Open Owner / Admin Portal (http://127.0.0.1:8000/owner/login)
echo     [T] Open Reservation Tracker (http://127.0.0.1:8000/track)
echo     [C] Clear Application Caches (artisan optimize:clear)
echo     [M] Run Database Migrations (artisan migrate)
echo     [R] Restart All Dev Servers
echo     [K] Stop All Dev Servers
echo     [Q] Stop All Dev Servers and Exit
echo.
echo ===============================================================================
choice /c OATCMRKQ /n /m "Select an option [O, A, T, C, M, R, K, Q]: "
if errorlevel 8 goto ACTION_QUIT
if errorlevel 7 goto ACTION_STOP
if errorlevel 6 goto ACTION_RESTART
if errorlevel 5 goto ACTION_MIGRATE
if errorlevel 4 goto ACTION_CACHE
if errorlevel 3 goto ACTION_TRACKER
if errorlevel 2 goto ACTION_ADMIN
if errorlevel 1 goto ACTION_OPEN
goto MENU

:ACTION_OPEN
start http://127.0.0.1:8000
goto MENU

:ACTION_ADMIN
start http://127.0.0.1:8000/owner/login
goto MENU

:ACTION_TRACKER
start http://127.0.0.1:8000/track
goto MENU

:ACTION_CACHE
echo.
echo [*] Clearing application caches...
php artisan optimize:clear
echo [OK] Cache cleared!
ping -n 3 127.0.0.1 >nul
goto MENU

:ACTION_MIGRATE
echo.
echo [*] Running migrations...
php artisan migrate
echo [OK] Migrations completed.
ping -n 3 127.0.0.1 >nul
goto MENU

:ACTION_RESTART
echo.
echo [*] Restarting all development servers...
goto START_SERVERS

:ACTION_STOP
echo.
call "%~dp0stop-dev.bat"
ping -n 3 127.0.0.1 >nul
goto MENU

:ACTION_QUIT
echo.
call "%~dp0stop-dev.bat"
echo Development session ended. Goodbye!
ping -n 2 127.0.0.1 >nul
exit /b 0
