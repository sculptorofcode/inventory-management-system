#!/bin/bash
# Database Migration Helper Script
# Run this script to manage database migrations

while true; do
    clear
    echo "IMS Database Migration Tool"
    echo "=========================="
    echo
    echo "Choose an option:"
    echo "1) Create new migration"
    echo "2) Run migrations"
    echo "3) Show migration status"
    echo "4) Rollback last batch"
    echo "5) Rollback to specific batch"
    echo "6) Rollback all migrations"
    echo "7) Exit"
    echo

    read -p "Enter your choice (1-7): " choice

    if [ "$choice" = "1" ]; then
        read -p "Enter migration name: " name
        php migrate-cli.php create "$name"
    elif [ "$choice" = "2" ]; then
        php migrate-cli.php run
    elif [ "$choice" = "3" ]; then
        php migrate-cli.php status
    elif [ "$choice" = "4" ]; then
        php migrate-cli.php rollback
    elif [ "$choice" = "5" ]; then
        read -p "Enter batch number: " batch
        php migrate-cli.php rollback-batch $batch
    elif [ "$choice" = "6" ]; then
        echo "WARNING: This will rollback all migrations. This action cannot be undone."
        read -p "Are you sure you want to continue? (y/n): " confirm
        if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
            php migrate-cli.php rollback-all
        fi
    elif [ "$choice" = "7" ]; then
        exit 0
    else
        echo "Invalid option. Please try again."
    fi

    echo
    read -p "Press Enter to continue..."
done
