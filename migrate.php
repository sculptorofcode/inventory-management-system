<?php

/**
 * Database Migration System
 * 
 * This script handles database migrations for the IMS project.
 * It tracks migrations that have been run by filename and 
 * ensures each migration is executed only once.
 */

// Include required files
require_once 'includes/config/after-login.php';
require_once 'includes/functions/functions.php';
require_once 'includes/classes/DatabaseMigrator.php';

// Set the migrations directory path
define('MIGRATIONS_DIR', __DIR__ . '/database/migrations');

// Initialize the database migrator
$migrator = new DatabaseMigrator($conn, MIGRATIONS_DIR);
$migrator->setupMigrationsTable();

//
// Migration helper functions are now handled by the DatabaseMigrator class
//



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

// Web interface for migration

// Handle Ajax requests
if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_migrations') {
    // Initialize the response array
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    // Get sorting info
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
    $columns = isset($_POST['columns']) ? $_POST['columns'] : [];

    // Get all migrations
    $executedMigrations = $migrator->getExecutedMigrations();
    $availableMigrations = $migrator->getAvailableMigrations();
    $migrationDetails = $migrator->getMigrationDetails();

    // Filter migrations based on search term if provided
    $filteredMigrations = [];
    foreach ($availableMigrations as $migration) {
        if (empty($search) || stripos($migration, $search) !== false) {
            $filteredMigrations[] = $migration;
        }
    }

    // Get total count
    $totalRecords = count($availableMigrations);
    $totalFiltered = count($filteredMigrations);

    // Prepare data for sorting
    $sortableData = [];
    foreach ($filteredMigrations as $migration) {
        $isExecuted = in_array($migration, $executedMigrations);
        $status = $isExecuted ? 'Installed' : 'Pending';
        $batch = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['batch'] : 0;
        $executedAt = isset($migrationDetails[$migration]) ? $migrationDetails[$migration]['executed_at'] : '';

        $sortableData[] = [
            'migration' => $migration,
            'status' => $status,
            'batch' => $batch,
            'executed_at' => $executedAt,
            'status_raw' => $isExecuted ? 1 : 0, // For sorting by status
        ];
    }

    // Sort the data based on requested column
    usort($sortableData, function ($a, $b) use ($orderColumn, $orderDir, $columns) {
        $columnName = '';
        if (isset($columns[$orderColumn]['name'])) {
            $columnName = $columns[$orderColumn]['name'];
        }

        $keyToCompare = 'migration'; // Default sort by migration name

        // Set the correct key for comparison based on the column name
        switch ($columnName) {
            case 'status':
                $keyToCompare = 'status_raw'; // Use numeric value for status
                break;
            case 'batch':
                $keyToCompare = 'batch';
                break;
            case 'executed_at':
                $keyToCompare = 'executed_at';
                break;
            default:
                $keyToCompare = 'migration';
        }

        // Handle empty values for stable sorting
        if (empty($a[$keyToCompare]) && empty($b[$keyToCompare])) {
            return 0;
        } elseif (empty($a[$keyToCompare])) {
            return $orderDir === 'asc' ? -1 : 1;
        } elseif (empty($b[$keyToCompare])) {
            return $orderDir === 'asc' ? 1 : -1;
        }

        // Compare the actual values
        if ($a[$keyToCompare] == $b[$keyToCompare]) {
            return 0;
        }

        $result = ($a[$keyToCompare] < $b[$keyToCompare]) ? -1 : 1;
        return $orderDir === 'asc' ? $result : -$result;
    });

    // Apply pagination
    $paginatedData = array_slice($sortableData, $start, $length);

    // Build final data array for DataTables
    $data = [];
    foreach ($paginatedData as $item) {
        $statusClass = $item['status'] === 'Installed' ? 'status-installed' : 'status-pending';

        $data[] = [
            'migration' => $item['migration'],
            'status' => '<span class="migration-status ' . $statusClass . '">' . $item['status'] . '</span>',
            'batch' => $item['batch'] ?: '-',
            'executed_at' => $item['executed_at'] ?: '-'
        ];
    }

    // Prepare the response for DataTables
    $response = [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalFiltered,
        'data' => $data
    ];

    // Send JSON response and exit
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
if(isset($_POST['action']) && $_POST['action'] === 'create' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    // Handle AJAX request to create migration
    $migrationName = isset($_POST['migration_name']) ? $_POST['migration_name'] : '';
    $response = ['message' => '', 'status' => 'error'];
    if (!empty($migrationName)) {
        $filePath = createMigration($migrationName);
        if ($filePath) {
            $response['status'] = 'success';
            $response['message'] = "✓ Created migration: " . basename($filePath);
            $response['function'] = 'reloadMigrationsTable';
        } else {
            $response['message'] = "Error creating migration.";
        }
    } else {
        $response['message'] = "Error: Migration name is required.";
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle other actions
if (isset($_POST['action']) && $_POST['action'] === 'run' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    ob_start();
    runMigrations();
    $output = ob_get_clean();
    $response = ['status' => 'success', 'message' => 'Migration completed successfully.', 'output' => $output, 'function' => 'reloadMigrationsTable'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'rollback' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    ob_start();
    rollbackLastBatch();
    $output = ob_get_clean();
    $response = ['status' => 'success', 'message' => 'Rollback completed successfully.', 'output' => $output, 'function' => 'reloadMigrationsTable'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'rollback-batch' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
    ob_start();
    if ($batch > 0) {
        rollbackToBatch($batch);
    } else {
        echo "Invalid batch number.";
    }
    $output = ob_get_clean();
    $response = ['status' => 'success', 'message' => 'Rollback to batch completed successfully.', 'output' => $output, 'function' => 'reloadMigrationsTable'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'rollback-all' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    ob_start();
    rollbackAll();
    $output = ob_get_clean();
    $response = ['status' => 'success', 'message' => 'All migrations rolled back successfully.', 'output' => $output, 'function' => 'reloadMigrationsTable'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
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
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .migration-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .migration-actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .migration-table {
            width: 100%;
            border-collapse: collapse;
        }

        .migration-table th,
        .migration-table td {
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

        .batch-info {
            margin-top: 30px;
        }

        .rollback-form {
            margin-bottom: 10px;
            display: inline-block;
        }

        .rollback-form .btn-danger {
            margin-right: 5px;
        }

        .rollback-batch-form .form-group {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './includes/layouts/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './includes/layouts/navbar.php'; ?>
                <div class="content-wrapper">

                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="container-fluid">
                            <div class="migration-header">
                                <h4 class="mb-0">Database Migrations</h4>
                                <p class="text-muted">Manage your database schema changes</p>
                            </div> <?php
                                    // Check for actions (non-Ajax only)
                                    $output = '';
                                    if (isset($_POST['action']) && (!isset($_POST['ajax']) || $_POST['ajax'] !== 'true')) {
                                        ob_start();

                                        switch ($_POST['action']) {
                                            case 'create':
                                                $migrationName = isset($_POST['migration_name']) ? $_POST['migration_name'] : '';
                                                if (!empty($migrationName)) {
                                                    createMigration($migrationName);
                                                } else {
                                                    echo "Error: Migration name is required.";
                                                }
                                                break;
                                            case 'run':
                                                runMigrations();
                                                break;
                                            case 'rollback':
                                                rollbackLastBatch();
                                                break;
                                            case 'rollback-batch':
                                                $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
                                                if ($batch > 0) {
                                                    rollbackToBatch($batch);
                                                } else {
                                                    echo "Invalid batch number.";
                                                }
                                                break;
                                            case 'rollback-all':
                                                rollbackAll();
                                                break;
                                        }

                                        $output = ob_get_clean();
                                    }

                                    // Get migration status for display
                                    $migrator->setupMigrationsTable();
                                    $executedMigrations = $migrator->getExecutedMigrations();
                                    $availableMigrations = $migrator->getAvailableMigrations();
                                    $migrationDetails = $migrator->getMigrationDetails();
                                    $batchDetails = $migrator->getBatchDetails();
                                    $latestBatch = $migrator->getLatestBatchNumber();
                                    ?>

                            <!-- Migration Actions Card -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <!-- Create migration form -->
                                    <form method="post" action="" class="migration-form mb-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-9 mb-2 mb-md-0">
                                                <input type="hidden" name="action" value="create">
                                                <input type="hidden" name="ajax" value="true">
                                                <input type="text" name="migration_name" class="form-control"
                                                    placeholder="Migration name (e.g., Add user roles)" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-success w-100">Create Migration</button>
                                            </div>
                                        </div>
                                    </form>

                                    <hr class="my-4">

                                    <div class="d-flex flex-wrap gap-2">
                                        <!-- Run migrations button -->
                                        <form method="post" action="" class="migration-form me-2">
                                            <input type="hidden" name="action" value="run">
                                            <input type="hidden" name="ajax" value="true">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-play-circle me-1"></i> Run Migrations
                                            </button>
                                        </form>

                                        <!-- Rollback actions -->
                                        <div id="rollback-buttons-container" class="d-flex flex-wrap gap-2" <?= $latestBatch > 0 ? '' : 'style="display:none"' ?>>
                                            <form method="post" action="" class="migration-form">
                                                <input type="hidden" name="action" value="rollback">
                                                <input type="hidden" name="ajax" value="true">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bx bx-undo me-1"></i> Rollback Last Batch
                                                </button>
                                            </form>

                                            <form method="post" action="" class="migration-form d-flex align-items-center gap-2">
                                                <input type="hidden" name="action" value="rollback-batch">
                                                <input type="hidden" name="ajax" value="true">
                                                <div id="batch-dropdown-container">
                                                    <select name="batch" class="form-select" required>
                                                        <?php for ($i = 1; $i <= $latestBatch; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bx bx-rewind me-1"></i> Rollback to Batch
                                                </button>
                                            </form>

                                            <form method="post" action="" class="migration-form">
                                                <input type="hidden" name="action" value="rollback-all">
                                                <input type="hidden" name="ajax" value="true">
                                                <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('Are you sure you want to rollback all migrations? This cannot be undone.')">
                                                    <i class="bx bx-reset me-1"></i> Rollback All
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Output container for messages -->
                            <div class="output-container mb-4" style="display: <?= !empty($output) ? 'block' : 'none' ?>"><?= nl2br(htmlspecialchars($output)) ?></div> <!-- Migrations table -->
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Available Migrations</h5>
                                </div>
                                <div class="card-body">
                                    <div id="migrations-table-container">
                                        <div class="table-responsive">
                                            <table id="migrationsDataTable" class="migration-table table table-hover dt-responsive nowrap" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>Migration File</th>
                                                        <th>Status</th>
                                                        <th>Batch</th>
                                                        <th>Executed At</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Batch information -->
                            <div id="batch-info-container">
                                <?php if (!empty($batchDetails)): ?>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Batch Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="migration-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Batch</th>
                                                            <th>Migrations</th>
                                                            <th>Executed At</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($batchDetails as $batch => $details): ?>
                                                            <tr>
                                                                <td><?= $batch ?></td>
                                                                <td><?= $details['count'] ?></td>
                                                                <td><?= $details['executed_at'] ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './includes/layouts/dash-footer.php'; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/layouts/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Initialize DataTable for migrations
            const migrationsTable = $('#migrationsDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'migrate.php?action=get_migrations',
                    type: 'POST'
                },
                columns: [{
                        data: 'migration',
                        name: 'migration'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'batch',
                        name: 'batch'
                    },
                    {
                        data: 'executed_at',
                        name: 'executed_at'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No migrations found',
                    zeroRecords: 'No matching migrations found'
                },
                responsive: true,
                columnDefs: [{
                    className: "dt-nowrap",
                    targets: [0, 3]
                }]
            });

            // Reload the DataTable after operations
            window.reloadMigrationsTable = function() {
                migrationsTable.ajax.reload(null, false);
            }

            // Function to show output
            function showOutput(output) {
                if (output) {
                    $(".output-container").show().html(output);

                    // Add card styling to the output container
                    $(".output-container").addClass("card p-3 mb-4");
                } else {
                    $(".output-container").hide();
                }
            }
        });
    </script>
</body>

</html>
<?php
