@echo off
setlocal enabledelayedexpansion
title Paddle Field - Stop Dev Services

echo ===============================================================================
echo              Stopping Paddle Field Development Services
echo ===============================================================================

set "STOPPED=0"

:: 1. Terminate processes on port 8000 (Laravel Server)
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":8000" ^| findstr "LISTENING"') do (
    echo [*] Stopping Laravel Server [Port 8000, PID: %%a]...
    taskkill /F /PID %%a >nul 2>&1
    set "STOPPED=1"
)

:: 2. Terminate processes on port 8085 (Laravel Reverb)
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":8085" ^| findstr "LISTENING"') do (
    echo [*] Stopping Laravel Reverb [Port 8085, PID: %%a]...
    taskkill /F /PID %%a >nul 2>&1
    set "STOPPED=1"
)

:: 3. Terminate processes on port 5173 (Vite Dev Server)
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":5173" ^| findstr "LISTENING"') do (
    echo [*] Stopping Vite Dev Server [Port 5173, PID: %%a]...
    taskkill /F /PID %%a >nul 2>&1
    set "STOPPED=1"
)

if !STOPPED! equ 0 (
    echo [i] No active servers found on ports 8000, 8085, or 5173.
) else (
    echo [OK] All development servers stopped successfully.
)

echo ===============================================================================
