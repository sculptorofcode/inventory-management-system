<?php
// filepath: e:\SGP\5th Sem\Major Project\ims-project\includes\classes\DatabaseMigrator.php

/**
 * DatabaseMigrator Class
 * 
 * Handles database migrations for the IMS project
 */
class DatabaseMigrator
{
    private $db;
    private $migrationsDir;

    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     * @param string $migrationsDir Path to migrations directory
     */
    public function __construct(PDO $db, $migrationsDir)
    {
        $this->db = $db;
        $this->migrationsDir = $migrationsDir;
    }
    
    /**
     * Create a new migration file
     * 
     * @param string $name Description of the migration
     * @return string The path to the created migration file
     */
    public function createMigrationFile($name)
    {
        // Format the filename
        $timestamp = date('Ymd_His');
        $filename = $timestamp . '_' . $this->formatMigrationName($name) . '.sql';
        $filePath = $this->migrationsDir . '/' . $filename;
        
        // Create the file with a template
        $template = "-- " . $filename . "\n";
        $template .= "-- " . $name . "\n";
        $template .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        $template .= "-- Your SQL statements go here\n\n\n";
        $template .= "-- Down: -- DROP TABLE IF EXISTS `table_name`;\n\n";
        
        // Check if the migrations directory exists
        if (!is_dir($this->migrationsDir)) {
            throw new Exception("Migrations directory does not exist: {$this->migrationsDir}");
        }
        
        // Write the file
        if (file_put_contents($filePath, $template) === false) {
            throw new Exception("Failed to create migration file: {$filePath}");
        }
        
        return $filePath;
    }
    
    /**
     * Format a migration name to be safe for filenames
     * 
     * @param string $name Migration description
     * @return string Safe filename component
     */
    private function formatMigrationName($name) 
    {
        // Replace spaces and special chars with underscores
        $safe = preg_replace('/[^a-z0-9]+/i', '_', $name);
        // Convert to lowercase
        $safe = strtolower($safe);
        // Remove trailing underscores
        $safe = trim($safe, '_');
        // Limit length
        $safe = substr($safe, 0, 100);
        
        return $safe;
    }

    /**
     * Setup the migrations table if it doesn't exist
     */
    public function setupMigrationsTable()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {                // Create the migrations table
                $migrationTableSql = file_get_contents($this->migrationsDir . '/20250603_create_migrations_table.sql');
                $this->db->exec($migrationTableSql);
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error setting up migrations table: " . $e->getMessage());
        }
    }

    /**
     * Get all migrations that have been executed
     * 
     * @return array List of executed migrations
     */
    public function getExecutedMigrations()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                return [];
            }

            $stmt = $this->db->query("SELECT migration FROM tbl_migrations ORDER BY id");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Error fetching migrations: " . $e->getMessage());
        }
    }

    /**
     * Get all available migration files
     * 
     * @return array List of migration files
     */
    public function getAvailableMigrations()
    {
        $files = scandir($this->migrationsDir);
        $migrations = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrations[] = $file;
            }
        }

        sort($migrations); // Sort migrations in alphabetical order
        return $migrations;
    }

    /**
     * Execute a migration file
     * 
     * @param string $migrationFile Migration file to execute
     * @return bool Whether the migration was successful
     */
    public function executeMigration($migrationFile)
    {
        try {
            $filePath = $this->migrationsDir . '/' . $migrationFile;
            $sql = file_get_contents($filePath);

            if (!$sql) {
                throw new Exception("Could not read migration file: $migrationFile");
            }

            // Execute the migration
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error executing migration $migrationFile: " . $e->getMessage());
        }
    }

    /**
     * Log a migration as executed
     * 
     * @param string $migrationFile Migration file that was executed
     * @param int $batch Batch number
     */
    public function logMigration($migrationFile, $batch)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO tbl_migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationFile, $batch]);
        } catch (PDOException $e) {
            throw new Exception("Error logging migration: " . $e->getMessage());
        }
    }

    /**
     * Get the next batch number
     * 
     * @return int Next batch number
     */
    public function getNextBatchNumber()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                return 1;
            }

            $result = $this->db->query("SELECT MAX(batch) as max_batch FROM tbl_migrations");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return ($row['max_batch'] ?? 0) + 1;
        } catch (PDOException $e) {
            throw new Exception("Error getting next batch number: " . $e->getMessage());
        }
    }

    /**
     * Get details of all migrations for display
     * 
     * @return array Migration details
     */
    public function getMigrationDetails()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                return [];
            }

            $stmt = $this->db->query("SELECT migration, batch, executed_at FROM tbl_migrations ORDER BY id");
            $details = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $details[$row['migration']] = $row;
            }

            return $details;
        } catch (PDOException $e) {
            throw new Exception("Error getting migration details: " . $e->getMessage());
        }
    }

    /**
     * Run all pending migrations
     * 
     * @return array Migration results
     */
    public function runMigrations()
    {
        $results = [
            'success' => true,
            'executed' => [],
            'errors' => []
        ];

        try {
            // Setup migrations table first
            $this->setupMigrationsTable();

            // Get executed migrations
            $executedMigrations = $this->getExecutedMigrations();

            // Get available migrations
            $availableMigrations = $this->getAvailableMigrations();

            // Find pending migrations
            $pendingMigrations = array_diff($availableMigrations, $executedMigrations);

            if (empty($pendingMigrations)) {
                return $results;
            }

            // Get the next batch number
            $batchNumber = $this->getNextBatchNumber();

            // Start the transaction before executing migrations
            $this->db->beginTransaction();

            foreach ($pendingMigrations as $migration) {
                try {
                    $this->executeMigration($migration);
                    $this->logMigration($migration, $batchNumber);
                    $results['executed'][] = $migration;
                } catch (Exception $e) {
                    throw new Exception("Error executing migration $migration: " . $e->getMessage());
                }
            }

            if($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get the latest batch number
     * 
     * @return int Latest batch number
     */
    public function getLatestBatchNumber()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                return 0;
            }

            $result = $this->db->query("SELECT MAX(batch) as max_batch FROM tbl_migrations");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return (int)($row['max_batch'] ?? 0);
        } catch (PDOException $e) {
            throw new Exception("Error getting latest batch number: " . $e->getMessage());
        }
    }

    /**
     * Get migrations from a specific batch
     * 
     * @param int $batchNumber Batch number
     * @return array Migrations from the batch
     */
    public function getMigrationsFromBatch($batchNumber)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, migration FROM tbl_migrations WHERE batch = ? ORDER BY id DESC");
            $stmt->execute([$batchNumber]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting migrations from batch: " . $e->getMessage());
        }
    }

    /**
     * Remove a migration from the log
     * 
     * @param int $migrationId Migration ID
     */
    public function removeMigration($migrationId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM tbl_migrations WHERE id = ?");
            $stmt->execute([$migrationId]);
        } catch (PDOException $e) {
            throw new Exception("Error removing migration: " . $e->getMessage());
        }
    }

    /**
     * Extract the name from a migration filename
     * 
     * @param string $migrationFile Migration filename
     * @return string Migration name
     */
    protected function extractMigrationName($migrationFile)
    {
        $nameParts = explode('_', pathinfo($migrationFile, PATHINFO_FILENAME), 2);
        if (count($nameParts) > 1) {
            return $nameParts[1];
        }
        return pathinfo($migrationFile, PATHINFO_FILENAME);
    }
    
    /**
     * Find the down SQL for a migration
     * Looks for a comment in the format: -- Down: SQL statements
     * 
     * @param string $migrationFile Migration filename
     * @return array Array of down SQL statements (strings)
     */
    protected function findDownSQL($migrationFile)
    {
        $filePath = $this->migrationsDir . '/' . $migrationFile;
        $content = file_get_contents($filePath);

        if (!$content) {
            return [];
        }

        // Try to find a full down SQL command (everything after "-- Down:" line)
        if (preg_match('/^\s*--\s*Down:\s*(.*)$/mi', $content, $match)) {
            $downSql = trim($match[1]);
            if (!empty($downSql)) {
                // Split by semicolon, filter out empty statements
                $statements = array_filter(array_map('trim', explode(';', $downSql)));
                return $statements;
            }
        }

        // Try to auto-generate rollback SQL based on the migration name
        $migrationName = $this->extractMigrationName($migrationFile);

        // Common patterns for auto-generating rollback SQL
        if (preg_match('/^create_(.+?)_table$/', $migrationName, $matches)) {
            $tableName = $matches[1];
            $tableName = str_replace('__', '_', $tableName);
            return ["DROP TABLE IF EXISTS `{$tableName}`"];
        }

        if (preg_match('/^add_(.+?)_to_(.+?)$/', $migrationName, $matches)) {
            $columns = explode('_and_', $matches[1]);
            $tableName = $matches[2];

            $dropColumns = array_map(function ($col) {
                return "DROP COLUMN `$col`";
            }, $columns);

            return ["ALTER TABLE `{$tableName}` " . implode(', ', $dropColumns)];
        }

        // If no pattern matched, return empty array
        return [];
    }


    /**
     * Roll back a migration
     * 
     * @param string $migrationFile Migration filename
     * @return bool Whether the rollback was successful
     */
    public function rollbackMigration($migrationFile)
    {
        try {
            $downSQL = $this->findDownSQL($migrationFile);

            if (empty($downSQL)) {
                throw new Exception("No down SQL found for migration: $migrationFile");
            }

            foreach ($downSQL as $statement) {
                print_r("Executing rollback statement: $statement\n");
                if (!empty($statement)) {
                    try {
                        $this->db->exec($statement . ';');
                    } catch (PDOException $e) {
                        // Log the statement that failed but continue with others
                        error_log("Failed statement in $migrationFile: $statement");
                        // Don't throw here, continue with other statements
                    }
                }
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Error rolling back migration $migrationFile: " . $e->getMessage());
        }
    }
    /**
     * Roll back the last batch of migrations
     * 
     * @return array Rollback results
     */
    public function rollbackLastBatch()
    {
        $latestBatch = $this->getLatestBatchNumber();
        if ($latestBatch > 0) {
            return $this->rollbackToBatch($latestBatch);
        } else {
            return ['success' => true, 'rolledBack' => [], 'errors' => []];
        }
    }
    /**
     * Roll back to a specific batch
     * 
     * @param int $batchNumber The batch to roll back to (exclusive)
     * @return array Rollback results
     */
    public function rollbackToBatch($batchNumber)
    {
        $results = [
            'success' => true,
            'rolledBack' => [],
            'errors' => []
        ];

        try {
            // Check if there are migrations to roll back
            $latestBatch = $this->getLatestBatchNumber();

            if ($latestBatch < $batchNumber) {
                return $results;
            }

            // Get migrations from all batches to roll back before starting transaction
            $migrationsToRollback = [];
            for ($batch = $latestBatch; $batch >= $batchNumber; $batch--) {
                $batchMigrations = $this->getMigrationsFromBatch($batch);
                foreach ($batchMigrations as $migration) {
                    $migrationsToRollback[] = $migration;
                }
            }

            // If no migrations found, return early
            if (empty($migrationsToRollback)) {
                return $results;
            }

            // Start transaction
            $this->db->beginTransaction();

            foreach ($migrationsToRollback as $migration) {
                try {
                    $this->rollbackMigration($migration['migration']);
                    $this->removeMigration($migration['id']);
                    $results['rolledBack'][] = $migration['migration'];
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $results['success'] = false;
                    $results['errors'][] = "Error rolling back {$migration['migration']}: " . $e->getMessage();
                    return $results;
                }
            }

            if($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Roll back all migrations
     * 
     * @return array Rollback results
     */
    public function rollbackAll()
    {
        return $this->rollbackToBatch(1);
    }

    /**
     * Get a list of batches with their migrations
     * 
     * @return array Batch details
     */
    public function getBatchDetails()
    {
        try {
            // Check if migrations table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'tbl_migrations'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                return [];
            }

            $stmt = $this->db->query("SELECT batch, COUNT(*) as migration_count, 
                                      MAX(executed_at) as executed_at 
                                      FROM tbl_migrations 
                                      GROUP BY batch 
                                      ORDER BY batch");

            $batches = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $batches[$row['batch']] = [
                    'count' => $row['migration_count'],
                    'executed_at' => $row['executed_at']
                ];
            }

            return $batches;
        } catch (PDOException $e) {
            throw new Exception("Error getting batch details: " . $e->getMessage());
        }
    }
}
