<?php

namespace Tracks\ActiveRecord;

abstract class Migration
{
    protected Connection $connection;
    protected Schema $schema;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->schema = new Schema($this->connection);
    }
    
    abstract public function up(): void;
    abstract public function down(): void;
    
    protected function createTable(string $name, callable $callback): void
    {
        $table = new TableDefinition($name);
        $callback($table);
        $this->schema->createTable($table);
    }
    
    protected function dropTable(string $name): void
    {
        $this->schema->dropTable($name);
    }
    
    protected function addColumn(string $table, string $column, string $type, array $options = []): void
    {
        $this->schema->addColumn($table, $column, $type, $options);
    }
    
    protected function removeColumn(string $table, string $column): void
    {
        $this->schema->removeColumn($table, $column);
    }
    
    protected function addIndex(string $table, $columns, array $options = []): void
    {
        $this->schema->addIndex($table, $columns, $options);
    }
    
    protected function removeIndex(string $table, $columns): void
    {
        $this->schema->removeIndex($table, $columns);
    }
    
    protected function execute(string $sql): void
    {
        $this->connection->execute($sql);
    }
}

class TableDefinition
{
    private string $name;
    private array $columns = [];
    private array $indexes = [];
    
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id();
        $this->timestamps();
    }
    
    public function id(): self
    {
        $this->columns['id'] = [
            'type' => 'integer',
            'primary_key' => true,
            'auto_increment' => true
        ];
        return $this;
    }
    
    public function string(string $name, int $limit = 255): self
    {
        $this->columns[$name] = [
            'type' => 'string',
            'limit' => $limit
        ];
        return $this;
    }
    
    public function text(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'text'
        ];
        return $this;
    }
    
    public function integer(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'integer'
        ];
        return $this;
    }
    
    public function bigint(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'bigint'
        ];
        return $this;
    }
    
    public function float(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'float'
        ];
        return $this;
    }
    
    public function decimal(string $name, int $precision = 10, int $scale = 2): self
    {
        $this->columns[$name] = [
            'type' => 'decimal',
            'precision' => $precision,
            'scale' => $scale
        ];
        return $this;
    }
    
    public function boolean(string $name, bool $default = false): self
    {
        $this->columns[$name] = [
            'type' => 'boolean',
            'default' => $default
        ];
        return $this;
    }
    
    public function date(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'date'
        ];
        return $this;
    }
    
    public function datetime(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'datetime'
        ];
        return $this;
    }
    
    public function timestamp(string $name): self
    {
        $this->columns[$name] = [
            'type' => 'timestamp'
        ];
        return $this;
    }
    
    public function timestamps(): self
    {
        $this->datetime('created_at');
        $this->datetime('updated_at');
        return $this;
    }
    
    public function references(string $name, array $options = []): self
    {
        $columnName = $name . '_id';
        $this->integer($columnName);
        
        if ($options['index'] ?? true) {
            $this->index($columnName);
        }
        
        return $this;
    }
    
    public function index($columns, array $options = []): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        
        $this->indexes[] = [
            'columns' => $columns,
            'unique' => $options['unique'] ?? false,
            'name' => $options['name'] ?? null
        ];
        
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    public function getIndexes(): array
    {
        return $this->indexes;
    }
}

class Schema
{
    private Connection $connection;
    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    public function createTable(TableDefinition $table): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$table->getName()} (";
        $columnDefinitions = [];
        
        foreach ($table->getColumns() as $name => $definition) {
            $columnDefinitions[] = $this->buildColumnDefinition($name, $definition);
        }
        
        $sql .= implode(', ', $columnDefinitions);
        $sql .= ")";
        
        $this->connection->execute($sql);
        
        foreach ($table->getIndexes() as $index) {
            $this->createIndex($table->getName(), $index);
        }
    }
    
    public function dropTable(string $name): void
    {
        $sql = "DROP TABLE IF EXISTS $name";
        $this->connection->execute($sql);
    }
    
    public function addColumn(string $table, string $column, string $type, array $options = []): void
    {
        $definition = array_merge(['type' => $type], $options);
        $columnDef = $this->buildColumnDefinition($column, $definition);
        $sql = "ALTER TABLE $table ADD COLUMN $columnDef";
        $this->connection->execute($sql);
    }
    
    public function removeColumn(string $table, string $column): void
    {
        $sql = "ALTER TABLE $table DROP COLUMN $column";
        $this->connection->execute($sql);
    }
    
    public function addIndex(string $table, $columns, array $options = []): void
    {
        $index = [
            'columns' => is_array($columns) ? $columns : [$columns],
            'unique' => $options['unique'] ?? false,
            'name' => $options['name'] ?? null
        ];
        $this->createIndex($table, $index);
    }
    
    public function removeIndex(string $table, $columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $indexName = 'idx_' . $table . '_' . implode('_', $columns);
        $sql = "DROP INDEX $indexName ON $table";
        $this->connection->execute($sql);
    }
    
    private function buildColumnDefinition(string $name, array $definition): string
    {
        $sql = "$name ";
        
        switch ($definition['type']) {
            case 'string':
                $limit = $definition['limit'] ?? 255;
                $sql .= "VARCHAR($limit)";
                break;
            case 'text':
                $sql .= "TEXT";
                break;
            case 'integer':
                $sql .= "INTEGER";
                break;
            case 'bigint':
                $sql .= "BIGINT";
                break;
            case 'float':
                $sql .= "FLOAT";
                break;
            case 'decimal':
                $precision = $definition['precision'] ?? 10;
                $scale = $definition['scale'] ?? 2;
                $sql .= "DECIMAL($precision, $scale)";
                break;
            case 'boolean':
                $sql .= "BOOLEAN";
                break;
            case 'date':
                $sql .= "DATE";
                break;
            case 'datetime':
                $sql .= "DATETIME";
                break;
            case 'timestamp':
                $sql .= "TIMESTAMP";
                break;
        }
        
        if ($definition['primary_key'] ?? false) {
            $sql .= " PRIMARY KEY";
        }
        
        if ($definition['auto_increment'] ?? false) {
            $sql .= " AUTO_INCREMENT";
        }
        
        if (isset($definition['null']) && !$definition['null']) {
            $sql .= " NOT NULL";
        }
        
        if (isset($definition['default'])) {
            $default = $definition['default'];
            if (is_bool($default)) {
                $default = $default ? 1 : 0;
            } elseif (is_string($default)) {
                $default = "'" . $default . "'";
            }
            $sql .= " DEFAULT $default";
        }
        
        return $sql;
    }
    
    private function createIndex(string $table, array $index): void
    {
        $columns = $index['columns'];
        $indexName = $index['name'] ?? 'idx_' . $table . '_' . implode('_', $columns);
        $unique = $index['unique'] ? 'UNIQUE' : '';
        
        $sql = "CREATE $unique INDEX $indexName ON $table (" . implode(', ', $columns) . ")";
        $this->connection->execute($sql);
    }
}