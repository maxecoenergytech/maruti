@echo off
title Maruti CRM - 1-Click Push to GitHub
color 0a

echo =======================================================================
echo               MARUTI SALES & SERVICES (GUWAHATI)
echo                   1-CLICK GITHUB SYNC LAUNCHER
echo =======================================================================
echo.

:: 1. Verify Git Installation
where git >nul 2>nul
if %errorlevel% neq 0 (
    color 0c
    echo [ERROR] Git is not found in your system PATH!
    echo Please install Git from https://git-scm.com/
    pause
    exit /b 1
)

:: 2. Ensure Remote is Configured
git remote get-url origin >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] Setting up GitHub remote...
    git remote add origin https://github.com/maxecoenergytech/maruti.git
)

:: 3. Check for Changes
echo [1/4] Checking repository status...
git status --porcelain > "%temp%\git_status.tmp"

for %%R in ("%temp%\git_status.tmp") do if %%~zR equ 0 (
    echo.
    echo [INFO] No new changes detected. Pushing latest commits to GitHub...
    del "%temp%\git_status.tmp"
    goto PUSH_STEP
)
del "%temp%\git_status.tmp"

:: 4. Ask for Commit Message (or use automatic default)
echo.
set "COMMIT_MSG="
set /p "COMMIT_MSG=Enter update note (Press ENTER to use auto timestamp): "

if "%COMMIT_MSG%"=="" (
    for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set DATE_STR=%%c-%%a-%%b
    for /f "tokens=1-2 delims=: " %%a in ('time /t') do set TIME_STR=%%a:%%b
    set "COMMIT_MSG=Update Maruti CRM & Quotation Maker (%DATE% %TIME%)"
)

:: 5. Stage & Commit
echo.
echo [2/4] Staging updated files...
git add -A

echo [3/4] Committing: "%COMMIT_MSG%"...
git commit -m "%COMMIT_MSG%"

:PUSH_STEP
:: 6. Push to GitHub
echo.
echo [4/4] Pushing to GitHub (branch: main)...
echo.
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo =======================================================================
    echo   SUCCESS! All updates have been pushed to GitHub.
    echo.
    echo   Repository: https://github.com/maxecoenergytech/maruti
    echo   Live Web App: https://maxecoenergytech.github.io/maruti/
    echo =======================================================================
) else (
    echo.
    color 0c
    echo =======================================================================
    echo   [FAILED] Push could not complete.
    echo   Please check your internet connection or GitHub login credentials.
    echo =======================================================================
)

echo.
echo Press any key to close this window...
pause >nul
exit
