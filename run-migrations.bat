@echo off
REM Database Migration Helper Script
REM Run this script to manage database migrations

:menu
cls
echo IMS Database Migration Tool
echo ==========================
echo.
echo Choose an option:
echo 1) Create new migration
echo 2) Run migrations
echo 3) Show migration status
echo 4) Rollback last batch
echo 5) Rollback to specific batch
echo 6) Rollback all migrations
echo 7) Exit
echo.

set /p choice=Enter your choice (1-7): 

if "%choice%"=="1" (
    set /p name=Enter migration name: 
    php migrate-cli.php create "%name%"
    goto pause_and_return
) else if "%choice%"=="2" (
    php migrate-cli.php run
    goto pause_and_return
) else if "%choice%"=="3" (
    php migrate-cli.php status
    goto pause_and_return
) else if "%choice%"=="4" (
    php migrate-cli.php rollback
    goto pause_and_return
) else if "%choice%"=="5" (
    set /p batch=Enter batch number: 
    php migrate-cli.php rollback-batch %batch%
    goto pause_and_return
) else if "%choice%"=="6" (
    echo WARNING: This will rollback all migrations. This action cannot be undone.
    set /p confirm=Are you sure you want to continue? (y/n): 
    if /i "%confirm%"=="y" (
        php migrate-cli.php rollback-all
    )
    goto pause_and_return
) else if "%choice%"=="7" (
    exit
) else (
    echo Invalid option. Please try again.
    goto pause_and_return
)

:pause_and_return
echo.
pause
goto menu
