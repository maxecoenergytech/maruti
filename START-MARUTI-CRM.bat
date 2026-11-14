@echo off
title Maruti Sales & Services CRM Launcher
color 0b
echo =======================================================================
echo               MARUTI SALES & SERVICES (GUWAHATI)
echo                      CRM & QUOTATION SYSTEM
echo =======================================================================
echo.
echo [1/2] Checking local CRM server status...

netstat -ano | findstr 127.0.0.1:8000 | findstr LISTENING >nul
if %errorlevel% neq 0 (
    echo [2/2] Starting local background server...
    start /min "Maruti CRM Server" cmd /c "php artisan serve --host=127.0.0.1 --port=8000"
    timeout /t 2 /nobreak >nul
) else (
    echo [2/2] Server is already running.
)

echo.
echo Opening Maruti CRM in your browser...
start http://127.0.0.1:8000/admin

echo.
echo =======================================================================
echo  CRM is now running at: http://127.0.0.1:8000/admin
echo  Default Login:
echo    - Admin: admin@marutisales.in / Admin@123
echo    - Executive: rahul@marutisales.in / Sales@123
echo =======================================================================
echo You may minimize this window.
timeout /t 5 >nul
exit
