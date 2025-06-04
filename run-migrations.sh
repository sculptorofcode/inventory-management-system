#!/bin/bash
# Database Migration Helper Script
# Run this script to manage database migrations

while true; do
    echo "IMS Database Migration Tool"
    echo "=========================="
    echo
    echo "Choose an option:"
    echo "1) Run migrations"
    echo "2) Show migration status"
    echo "3) Exit"
    echo

    read -p "Enter your choice (1-3): " choice

    if [ "$choice" = "1" ]; then
        php migrate.php run
    elif [ "$choice" = "2" ]; then
        php migrate.php status
    elif [ "$choice" = "3" ]; then
        exit 0
    else
        echo "Invalid option. Please try again."
    fi

    echo
    read -p "Press Enter to continue..."
    clear
done
