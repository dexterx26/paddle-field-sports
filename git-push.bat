@echo off
setlocal enabledelayedexpansion

title Paddle Field Sports Center - Git Auto Commit & Push

:: Navigate to the repository root directory
cd /d "%~dp0"

echo ===============================================================================
echo            PADDLE FIELD SPORTS CENTER - GIT AUTO COMMIT ^& PUSH
echo ===============================================================================
echo.

:: 1. Check if Git is installed/available
where git >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\Program Files\Git\cmd\git.exe" (
        set "PATH=C:\Program Files\Git\cmd;!PATH!"
    ) else if exist "C:\Program Files (x86)\Git\cmd\git.exe" (
        set "PATH=C:\Program Files (x86)\Git\cmd;!PATH!"
    ) else (
        echo [ERROR] Git was not found in your PATH or standard install directory.
        echo Please ensure Git is installed (https://git-scm.com).
        echo.
        pause
        exit /b 1
    )
)

:: 2. Verify git repository exists
if not exist ".git" (
    echo [ERROR] Not a git repository. Missing .git folder in %~dp0
    echo.
    pause
    exit /b 1
)

:: 3. Get current branch name
for /f "tokens=*" %%b in ('git rev-parse --abbrev-ref HEAD 2^>nul') do (
    set "BRANCH=%%b"
)
if "!BRANCH!"=="" set "BRANCH=main"

echo [*] Repository: %~dp0
echo [*] Current branch: !BRANCH!
echo.

:: 4. Check status
echo [*] Checking working directory status...
git status -s > "%TEMP%\git_status.tmp" 2>nul

set /a CHANGES=0
for /f %%i in ("%TEMP%\git_status.tmp") do set /a CHANGES=%%~zi

if !CHANGES! gtr 0 (
    echo.
    echo [*] Modified and untracked files found:
    echo -------------------------------------------------------------------------------
    git status -s
    echo -------------------------------------------------------------------------------
    echo.

    :: Generate default timestamp
    for /f "tokens=1-3 delims=/.- " %%a in ("%date%") do set "D=%%a-%%b-%%c"
    for /f "tokens=1-2 delims=:." %%a in ("%time%") do set "T=%%a:%%b"
    set "DEFAULT_MSG=Auto-commit: %date% %time%"

    :: Prompt for custom commit message or default
    set "USER_MSG="
    echo Press ENTER to use default message [!DEFAULT_MSG!]
    set /p "USER_MSG=Or enter custom commit message: "

    if "!USER_MSG!"=="" (
        set "COMMIT_MSG=!DEFAULT_MSG!"
    ) else (
        set "COMMIT_MSG=!USER_MSG!"
    )

    echo.
    echo [*] Staging all files (git add -A)...
    git add -A

    echo [*] Committing: "!COMMIT_MSG!"...
    git commit -m "!COMMIT_MSG!"
    if !errorlevel! neq 0 (
        echo [WARNING] Commit encountered an issue or nothing to commit.
    ) else (
        echo [SUCCESS] Changes committed successfully!
    )
) else (
    echo [*] No modified or untracked files to commit.
)

if exist "%TEMP%\git_status.tmp" del "%TEMP%\git_status.tmp"

:: 5. Push to remote
echo.
echo [*] Pushing to remote branch (!BRANCH!)...
echo -------------------------------------------------------------------------------

:: Try regular push first
git push origin !BRANCH!
if !errorlevel! neq 0 (
    echo.
    echo [*] Upstream not set or rejected. Attempting push with upstream tracking...
    git push -u origin !BRANCH!
)

if !errorlevel! equ 0 (
    echo.
    echo ===============================================================================
    echo [SUCCESS] Git commit and push completed successfully!
    echo ===============================================================================
) else (
    echo.
    echo ===============================================================================
    echo [ERROR] Push failed. Please check your internet connection or git credentials.
    echo ===============================================================================
)

echo.
echo Press any key to exit...
pause >nul
