<?php
/**
 * Database Migration System (CLI Only)
 * 
 * This script handles database migrations for the IMS project.
 * This version is streamlined for CLI usage without session checks.
 */

// Include minimum required files
require_once 'includes/config/config.php';
require_once 'includes/functions/functions.php';
require_once 'includes/classes/DatabaseMigrator.php';

// Database connection
function db_connect()
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$conn = db_connect();

// Set the migrations directory path
define('MIGRATIONS_DIR', __DIR__ . '/database/migrations');

// Initialize the database migrator
$migrator = new DatabaseMigrator($conn, MIGRATIONS_DIR);

/**
 * Create a new migration file
 * 
 * @param string $name Description of the migration
 * @return string|false The path to the created migration file or false on failure
 */
function createMigration($name)
{
    global $migrator;

    try {
        if (empty($name)) {
            echo "Error: Migration name is required.\n";
            return false;
        }

        $filePath = $migrator->createMigrationFile($name);
        echo "✓ Created migration: " . basename($filePath) . "\n";
        return $filePath;
    } catch (Exception $e) {
        echo "Error creating migration: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Run all pending migrations
 */
function runMigrations()
{
    global $migrator;

    try {
        $availableMigrations = $migrator->getAvailableMigrations();
        $executedMigrations = $migrator->getExecutedMigrations();

        // Find pending migrations
        $pendingMigrations = array_diff($availableMigrations, $executedMigrations);

        if (empty($pendingMigrations)) {
            echo "No pending migrations found.\n";
            return;
        }

        echo "Running migrations...\n";

        $results = $migrator->runMigrations();

        if ($results['success']) {
            if (empty($results['executed'])) {
                echo "No migrations to run.\n";
            } else {
                foreach ($results['executed'] as $migration) {
                    echo "✓ Migrated:  $migration\n";
                }
                echo "Migration complete.\n";
            }
        } else {
            echo "Migration failed:\n";
            foreach ($results['errors'] as $error) {
                echo "- $error\n";
            }
        }
    } catch (Exception $e) {
        die("Migration error: " . $e->getMessage());
    }
}

/**
 * Roll back the last batch of migrations
 */
function rollbackLastBatch()
{
    global $migrator;

    try {
        echo "Rolling back the last batch of migrations...\n";

        $results = $migrator->rollbackLastBatch();

        if ($results['success']) {
            if (empty($results['rolledBack'])) {
                echo "No migrations to roll back.\n";
            } else {
                foreach ($results['rolledBack'] as $migration) {
                    echo "✓ Rolled back: $migration\n";
                }
                echo "Rollback complete.\n";
            }
        } else {
            echo "Rollback failed:\n";
            foreach ($results['errors'] as $error) {
                echo "- $error\n";
            }
        }
    } catch (Exception $e) {
        die("Rollback error: " . $e->getMessage());
    }
}

/**
 * Roll back to a specific batch
 * 
 * @param int $batch Batch number to roll back to (exclusive)
 */
function rollbackToBatch($batch)
{
    global $migrator;

    try {
        echo "Rolling back to batch $batch...\n";

        $results = $migrator->rollbackToBatch($batch);

        if ($results['success']) {
            if (empty($results['rolledBack'])) {
                echo "No migrations to roll back.\n";
            } else {
                foreach ($results['rolledBack'] as $migration) {
                    echo "✓ Rolled back: $migration\n";
                }
                echo "Rollback complete.\n";
            }
        } else {
            echo "Rollback failed:\n";
            foreach ($results['errors'] as $error) {
                echo "- $error\n";
            }
        }
    } catch (Exception $e) {
        die("Rollback error: " . $e->getMessage());
    }
}

/**
 * Roll back all migrations
 */
function rollbackAll()
{
    global $migrator;

    try {
        echo "Rolling back all migrations...\n";

        $results = $migrator->rollbackAll();

        if ($results['success']) {
            if (empty($results['rolledBack'])) {
                echo "No migrations to roll back.\n";
            } else {
                foreach ($results['rolledBack'] as $migration) {
                    echo "✓ Rolled back: $migration\n";
                }
                echo "Rollback complete.\n";
            }
        } else {
            echo "Rollback failed:\n";
            foreach ($results['errors'] as $error) {
                echo "- $error\n";
            }
        }
    } catch (Exception $e) {
        die("Rollback error: " . $e->getMessage());
    }
}

/**
 * Show migration status
 */
function migrationStatus()
{
    global $migrator;

    try {
        $migrator->setupMigrationsTable();

        // Get migration data
        $executedMigrations = $migrator->getExecutedMigrations();
        $availableMigrations = $migrator->getAvailableMigrations();
        $migrationDetails = $migrator->getMigrationDetails();
        $batchDetails = $migrator->getBatchDetails();

        echo "Migration Status:\n";
        echo str_repeat('-', 80) . "\n";
        echo sprintf("%-50s %-15s %s\n", "Migration", "Status", "Batch");
        echo str_repeat('-', 80) . "\n";

        foreach ($availableMigrations as $migration) {
            $status = in_array($migration, $executedMigrations) ? "Installed" : "Pending";
            $batch = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['batch'] : "-";

            echo sprintf("%-50s %-15s %s\n", $migration, $status, $batch);
        }

        echo str_repeat('-', 80) . "\n\n";

        // Display batch information
        if (!empty($batchDetails)) {
            echo "Batch Information:\n";
            echo str_repeat('-', 80) . "\n";
            echo sprintf("%-10s %-20s %s\n", "Batch", "Migrations", "Executed At");
            echo str_repeat('-', 80) . "\n";

            foreach ($batchDetails as $batch => $details) {
                echo sprintf(
                    "%-10s %-20s %s\n",
                    $batch,
                    $details['count'],
                    $details['executed_at']
                );
            }

            echo str_repeat('-', 80) . "\n";
        }
    } catch (Exception $e) {
        die("Error checking migration status: " . $e->getMessage());
    }
}

// Parse command line arguments
$action = $argv[1] ?? 'help';

switch ($action) {
    case 'create':
        $name = $argv[2] ?? '';
        if (empty($name)) {
            echo "Error: Migration name is required.\n";
            echo "Usage: php migrate-cli.php create \"Create users table\"\n";
            break;
        }
        createMigration($name);
        break;

    case 'run':
        runMigrations();
        break;

    case 'status':
        migrationStatus();
        break;

    case 'rollback':
        rollbackLastBatch();
        break;

    case 'rollback-batch':
        $batch = isset($argv[2]) ? intval($argv[2]) : 0;
        if ($batch <= 0) {
            echo "Invalid batch number. Please specify a positive batch number.\n";
            break;
        }
        rollbackToBatch($batch);
        break;

    case 'rollback-all':
        rollbackAll();
        break;

    case 'help':
    default:
        echo "IMS Database Migration Tool\n\n";
        echo "Usage:\n";
        echo "  php migrate-cli.php [command] [options]\n\n";
        echo "Available commands:\n";
        echo "  create [name]    Create a new migration file\n";
        echo "                   Example: php migrate-cli.php create \"Add index to products\"\n";
        echo "  run              Run all pending migrations\n";
        echo "  status           Show migration status\n";
        echo "  rollback         Rollback the last batch of migrations\n";
        echo "  rollback-batch   Rollback to a specific batch number (inclusive)\n";
        echo "                   Example: php migrate-cli.php rollback-batch 2\n";
        echo "  rollback-all     Rollback all migrations\n";
        echo "  help             Show this help message\n";
        break;
}
