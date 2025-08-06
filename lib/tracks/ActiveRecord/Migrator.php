<?php

namespace Tracks\ActiveRecord;

class Migrator
{
    private Connection $connection;
    private string $migrationsPath;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->migrationsPath = dirname(__DIR__, 3) . '/db/migrate/';
        $this->ensureMigrationsTable();
    }
    
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS schema_migrations (
            version VARCHAR(255) PRIMARY KEY,
            migrated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->connection->execute($sql);
    }
    
    public function migrate(): void
    {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "No pending migrations.\n";
            return;
        }
        
        foreach ($migrations as $migration) {
            $this->runMigration($migration, 'up');
        }
    }
    
    public function rollback(int $steps = 1): void
    {
        $migrations = $this->getExecutedMigrations();
        $migrations = array_slice($migrations, -$steps);
        
        if (empty($migrations)) {
            echo "No migrations to rollback.\n";
            return;
        }
        
        foreach (array_reverse($migrations) as $migration) {
            $this->runMigration($migration, 'down');
        }
    }
    
    public function status(): void
    {
        $executed = $this->getExecutedVersions();
        $all = $this->getAllMigrations();
        
        echo "Migration Status:\n";
        echo str_repeat('-', 50) . "\n";
        
        foreach ($all as $migration) {
            $version = $this->getVersionFromFile($migration);
            $status = in_array($version, $executed) ? 'UP' : 'DOWN';
            echo "[$status] $migration\n";
        }
    }
    
    private function runMigration(string $file, string $direction): void
    {
        require_once $this->migrationsPath . $file;
        
        $className = $this->getClassNameFromFile($file);
        $migration = new $className();
        
        echo "Running migration: $file ($direction)...\n";
        
        $this->connection->beginTransaction();
        
        try {
            $migration->$direction();
            
            if ($direction === 'up') {
                $this->recordMigration($file);
            } else {
                $this->removeMigration($file);
            }
            
            $this->connection->commit();
            echo "Migration completed: $file\n";
        } catch (\Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }
    
    private function recordMigration(string $file): void
    {
        $version = $this->getVersionFromFile($file);
        $sql = "INSERT INTO schema_migrations (version) VALUES (?)";
        $this->connection->execute($sql, [$version]);
    }
    
    private function removeMigration(string $file): void
    {
        $version = $this->getVersionFromFile($file);
        $sql = "DELETE FROM schema_migrations WHERE version = ?";
        $this->connection->execute($sql, [$version]);
    }
    
    private function getPendingMigrations(): array
    {
        $all = $this->getAllMigrations();
        $executed = $this->getExecutedVersions();
        
        return array_filter($all, function($file) use ($executed) {
            $version = $this->getVersionFromFile($file);
            return !in_array($version, $executed);
        });
    }
    
    private function getExecutedMigrations(): array
    {
        $executed = $this->getExecutedVersions();
        $all = $this->getAllMigrations();
        
        return array_filter($all, function($file) use ($executed) {
            $version = $this->getVersionFromFile($file);
            return in_array($version, $executed);
        });
    }
    
    private function getAllMigrations(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }
        
        $files = scandir($this->migrationsPath);
        $migrations = array_filter($files, function($file) {
            return preg_match('/^\d{14}_.*\.php$/', $file);
        });
        
        sort($migrations);
        return array_values($migrations);
    }
    
    private function getExecutedVersions(): array
    {
        $sql = "SELECT version FROM schema_migrations ORDER BY version";
        $result = $this->connection->query($sql);
        
        $versions = [];
        while ($row = $result->fetch()) {
            $versions[] = $row['version'];
        }
        
        return $versions;
    }
    
    private function getVersionFromFile(string $file): string
    {
        return substr($file, 0, 14);
    }
    
    private function getClassNameFromFile(string $file): string
    {
        $name = substr($file, 15, -4);
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts);
    }
    
    public function create(string $name): string
    {
        $timestamp = date('YmdHis');
        $filename = $timestamp . '_' . $name . '.php';
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        
        $template = <<<PHP
<?php

use Tracks\ActiveRecord\Migration;

class $className extends Migration
{
    public function up(): void
    {
        // Add migration code here
    }
    
    public function down(): void
    {
        // Add rollback code here
    }
}
PHP;
        
        file_put_contents($this->migrationsPath . $filename, $template);
        
        return $filename;
    }
}