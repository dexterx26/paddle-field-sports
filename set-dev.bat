@echo off
cd /d "%~dp0"

where php >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" set "PATH=C:\xampp\php;!PATH!"
    if exist "D:\xampp\php\php.exe" set "PATH=D:\xampp\php;!PATH!"
)

echo [*] Switching Paddle Field Sports Center to DEV (SQLite)...
php artisan app:switch-env dev
echo.
pause
