<?php

namespace Tracks\ActiveRecord;

class Query
{
    private string $modelClass;
    private array $conditions = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $includes = [];
    private array $joins = [];
    
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }
    
    public function where($conditions): self
    {
        if (is_array($conditions)) {
            $this->conditions = array_merge($this->conditions, $conditions);
        } else {
            $this->conditions[] = $conditions;
        }
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }
    
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function includes(string ...$associations): self
    {
        $this->includes = array_merge($this->includes, $associations);
        return $this;
    }
    
    public function joins(string $table, string $condition): self
    {
        $this->joins[] = "JOIN $table ON $condition";
        return $this;
    }
    
    public function leftJoins(string $table, string $condition): self
    {
        $this->joins[] = "LEFT JOIN $table ON $condition";
        return $this;
    }
    
    public function get(): array
    {
        $sql = $this->buildSql();
        $params = $this->buildParams();
        
        $connection = $this->getConnection();
        $result = $connection->query($sql, $params);
        
        $records = [];
        while ($row = $result->fetch()) {
            $records[] = $this->modelClass::instantiate($row);
        }
        
        if (!empty($this->includes)) {
            $this->loadIncludes($records);
        }
        
        return $records;
    }
    
    public function first()
    {
        $this->limit(1);
        $records = $this->get();
        return $records[0] ?? null;
    }
    
    public function last()
    {
        $this->orderBy('id', 'DESC')->limit(1);
        $records = $this->get();
        return $records[0] ?? null;
    }
    
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM " . $this->getTableName();
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $connection = $this->getConnection();
        $result = $connection->query($sql, $this->buildParams());
        
        return (int) $result->fetchColumn();
    }
    
    public function exists(): bool
    {
        return $this->count() > 0;
    }
    
    public function pluck(string $column): array
    {
        $sql = "SELECT $column FROM " . $this->getTableName();
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $connection = $this->getConnection();
        $result = $connection->query($sql, $this->buildParams());
        
        $values = [];
        while ($row = $result->fetch()) {
            $values[] = $row[$column];
        }
        
        return $values;
    }
    
    public function update(array $attributes): int
    {
        $sets = [];
        $values = [];
        
        foreach ($attributes as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE " . $this->getTableName() . " SET " . implode(', ', $sets);
        
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
            $values = array_merge($values, $this->buildParams());
        }
        
        $connection = $this->getConnection();
        $connection->execute($sql, $values);
        
        return $connection->getPdo()->rowCount();
    }
    
    public function delete(): int
    {
        $sql = "DELETE FROM " . $this->getTableName();
        
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $connection = $this->getConnection();
        $connection->execute($sql, $this->buildParams());
        
        return $connection->getPdo()->rowCount();
    }
    
    private function buildSql(): string
    {
        $sql = "SELECT * FROM " . $this->getTableName();
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        
        return $sql;
    }
    
    private function buildWhereClause(): string
    {
        $where = [];
        
        foreach ($this->conditions as $key => $value) {
            if (is_string($key)) {
                if (is_array($value)) {
                    $placeholders = array_fill(0, count($value), '?');
                    $where[] = "$key IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $where[] = "$key = ?";
                }
            } else {
                $where[] = $value;
            }
        }
        
        return implode(' AND ', $where);
    }
    
    private function buildParams(): array
    {
        $params = [];
        
        foreach ($this->conditions as $key => $value) {
            if (is_string($key)) {
                if (is_array($value)) {
                    $params = array_merge($params, $value);
                } else {
                    $params[] = $value;
                }
            }
        }
        
        return $params;
    }
    
    private function loadIncludes(array &$records): void
    {
        // This would implement eager loading of associations
        // For now, keeping it simple
    }
    
    private function getTableName(): string
    {
        return $this->modelClass::tableName();
    }
    
    private function getConnection(): Connection
    {
        return $this->modelClass::getConnection();
    }
}