@echo off
REM Database Migration Helper Script
REM Run this script to manage database migrations

:menu
cls
echo IMS Database Migration Tool
echo ==========================
echo.
echo Choose an option:
echo 1) Run migrations
echo 2) Show migration status
echo 3) Exit
echo.

set /p choice=Enter your choice (1-3): 

if "%choice%"=="1" (
    php migrate.php run
    goto pause_and_return
) else if "%choice%"=="2" (
    php migrate.php status
    goto pause_and_return
) else if "%choice%"=="3" (
    exit
) else (
    echo Invalid option. Please try again.
    goto pause_and_return
)

:pause_and_return
echo.
pause
goto menu
