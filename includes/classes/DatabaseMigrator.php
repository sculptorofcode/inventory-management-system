<?php
// filepath: e:\SGP\5th Sem\Major Project\ims-project\includes\classes\DatabaseMigrator.php

/**
 * DatabaseMigrator Class
 * 
 * Handles database migrations for the IMS project
 */
class DatabaseMigrator {
    private $db;
    private $migrationsDir;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     * @param string $migrationsDir Path to migrations directory
     */
    public function __construct(PDO $db, $migrationsDir) {
        $this->db = $db;
        $this->migrationsDir = $migrationsDir;
    }
    
    /**
     * Setup the migrations table if it doesn't exist
     */
    public function setupMigrationsTable() {
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
    public function getExecutedMigrations() {
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
    public function getAvailableMigrations() {
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
    public function executeMigration($migrationFile) {
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
    public function logMigration($migrationFile, $batch) {
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
    public function getNextBatchNumber() {
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
    public function getMigrationDetails() {
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
    public function runMigrations() {
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
            
            $this->db->beginTransaction();
            
            foreach ($pendingMigrations as $migration) {
                try {
                    $this->executeMigration($migration);
                    $this->logMigration($migration, $batchNumber);
                    $results['executed'][] = $migration;
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $results['success'] = false;
                    $results['errors'][] = "Error executing $migration: " . $e->getMessage();
                    return $results;
                }
            }
            
            $this->db->commit();
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
}
