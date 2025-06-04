<?php
/**
 * Database Migration System
 * 
 * This script handles database migrations for the IMS project.
 * It tracks migrations that have been run by filename and 
 * ensures each migration is executed only once.
 */

// Include required files
require_once 'includes/config/database.php';
require_once 'includes/functions/functions.php';
require_once 'includes/classes/DatabaseMigrator.php';

// Set the migrations directory path
define('MIGRATIONS_DIR', __DIR__ . '/database/migrations');

// Initialize the database migrator
$migrator = new DatabaseMigrator($conn, MIGRATIONS_DIR);

//
// Migration helper functions are now handled by the DatabaseMigrator class
//



/**
 * Run all pending migrations
 */
function runMigrations() {
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
 * Show migration status
 */
function migrationStatus() {
    global $migrator;
    
    try {
        $migrator->setupMigrationsTable();
        
        // Get migration data
        $executedMigrations = $migrator->getExecutedMigrations();
        $availableMigrations = $migrator->getAvailableMigrations();
        $migrationDetails = $migrator->getMigrationDetails();
        
        echo "Migration Status:\n";
        echo str_repeat('-', 80) . "\n";
        echo sprintf("%-50s %-15s %s\n", "Migration", "Status", "Batch");
        echo str_repeat('-', 80) . "\n";
        
        foreach ($availableMigrations as $migration) {
            $status = in_array($migration, $executedMigrations) ? "Installed" : "Pending";
            $batch = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['batch'] : "-";
            
            echo sprintf("%-50s %-15s %s\n", $migration, $status, $batch);
        }
        
        echo str_repeat('-', 80) . "\n";
        
    } catch (Exception $e) {
        die("Error checking migration status: " . $e->getMessage());
    }
}

// Check if running in CLI mode
if (php_sapi_name() === 'cli') {
    // Parse command line arguments
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'run':
            runMigrations();
            break;
        
        case 'status':
            migrationStatus();
            break;
            
        case 'help':
        default:
            echo "IMS Database Migration Tool\n\n";
            echo "Usage:\n";
            echo "  php migrate.php [command]\n\n";
            echo "Available commands:\n";
            echo "  run      Run all pending migrations\n";
            echo "  status   Show migration status\n";
            echo "  help     Show this help message\n";
            break;
    }
} else {
    // Web interface for migration
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Migrations - <?= APP_NAME ?></title>
        <?php include 'includes/layouts/styles.php'; ?>
        <style>
            .migration-container {
                max-width: 900px;
                margin: 50px auto;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .migration-header {
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .migration-actions {
                margin: 20px 0;
            }
            .migration-table {
                width: 100%;
                border-collapse: collapse;
            }
            .migration-table th, .migration-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .migration-table th {
                background: #f5f5f5;
            }
            .migration-status {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: bold;
            }
            .status-installed {
                background: #d4edda;
                color: #155724;
            }
            .status-pending {
                background: #f8d7da;
                color: #721c24;
            }
            .output-container {
                margin-top: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 4px;
                border: 1px solid #ddd;
                max-height: 300px;
                overflow-y: auto;
                font-family: monospace;
                white-space: pre;
            }
        </style>
    </head>
    <body>
        <?php include 'includes/layouts/header.php'; ?>
        <?php include 'includes/layouts/navbar.php'; ?>
        <?php include 'includes/layouts/sidebar.php'; ?>
        
        <div class="layout-page">
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="migration-container">
                        <div class="migration-header">
                            <h4 class="mb-0">Database Migrations</h4>
                            <p class="text-muted">Manage your database schema changes</p>
                        </div>
                        
                <?php
                        // Check for actions
                        $output = '';
                        if (isset($_POST['action'])) {
                            ob_start();
                            
                            if ($_POST['action'] === 'run') {
                                runMigrations();
                            }
                            
                            $output = ob_get_clean();
                        }
                        
                        // Get migration status for display
                        $migrator->setupMigrationsTable();
                        $executedMigrations = $migrator->getExecutedMigrations();
                        $availableMigrations = $migrator->getAvailableMigrations();
                        $migrationDetails = $migrator->getMigrationDetails();
                        ?>
                        
                        <div class="migration-actions">
                            <form method="post" action="">
                                <button type="submit" name="action" value="run" class="btn btn-primary">Run Migrations</button>
                            </form>
                        </div>
                        
                        <?php if (!empty($output)): ?>
                        <div class="output-container"><?= nl2br(htmlspecialchars($output)) ?></div>
                        <?php endif; ?>
                        
                        <table class="migration-table">
                            <thead>
                                <tr>
                                    <th>Migration File</th>
                                    <th>Status</th>
                                    <th>Batch</th>
                                    <th>Executed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableMigrations as $migration): ?>
                                    <?php
                                    $isExecuted = in_array($migration, $executedMigrations);
                                    $status = $isExecuted ? 'Installed' : 'Pending';
                                    $statusClass = $isExecuted ? 'status-installed' : 'status-pending';
                                    $batch = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['batch'] : '-';
                                    $executedAt = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['executed_at'] : '-';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($migration) ?></td>
                                        <td><span class="migration-status <?= $statusClass ?>"><?= $status ?></span></td>
                                        <td><?= $batch ?></td>
                                        <td><?= $executedAt ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'includes/layouts/scripts.php'; ?>
    </body>
    </html>
    <?php
}
