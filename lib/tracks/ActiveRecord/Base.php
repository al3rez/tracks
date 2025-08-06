<?php

namespace Tracks\ActiveRecord;

class Base
{
    protected static ?Connection $connection = null;
    protected array $attributes = [];
    protected array $changes = [];
    protected bool $persisted = false;
    protected array $errors = [];
    
    protected static array $hasMany = [];
    protected static array $belongsTo = [];
    protected static array $hasOne = [];
    protected static array $validations = [];
    protected static array $callbacks = [];
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }
    
    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }
    
    public function __get(string $name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        
        return $this->attributes[$name] ?? null;
    }
    
    public function __set(string $name, $value): void
    {
        $this->setAttribute($name, $value);
    }
    
    protected function setAttribute(string $name, $value): void
    {
        if (!isset($this->attributes[$name]) || $this->attributes[$name] !== $value) {
            $this->changes[$name] = $value;
        }
        $this->attributes[$name] = $value;
    }
    
    public static function find($id): ?static
    {
        $sql = "SELECT * FROM " . static::tableName() . " WHERE id = ? LIMIT 1";
        $result = static::getConnection()->query($sql, [$id]);
        
        if ($row = $result->fetch()) {
            return static::instantiate($row);
        }
        
        return null;
    }
    
    public static function findBy(array $conditions): ?static
    {
        $where = [];
        $values = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
            $values[] = $value;
        }
        
        $sql = "SELECT * FROM " . static::tableName() . " WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $result = static::getConnection()->query($sql, $values);
        
        if ($row = $result->fetch()) {
            return static::instantiate($row);
        }
        
        return null;
    }
    
    public static function where(array $conditions): Query
    {
        $query = new Query(static::class);
        return $query->where($conditions);
    }
    
    public static function all(): array
    {
        $sql = "SELECT * FROM " . static::tableName();
        $result = static::getConnection()->query($sql);
        
        $records = [];
        while ($row = $result->fetch()) {
            $records[] = static::instantiate($row);
        }
        
        return $records;
    }
    
    public static function first(): ?static
    {
        $sql = "SELECT * FROM " . static::tableName() . " ORDER BY id ASC LIMIT 1";
        $result = static::getConnection()->query($sql);
        
        if ($row = $result->fetch()) {
            return static::instantiate($row);
        }
        
        return null;
    }
    
    public static function last(): ?static
    {
        $sql = "SELECT * FROM " . static::tableName() . " ORDER BY id DESC LIMIT 1";
        $result = static::getConnection()->query($sql);
        
        if ($row = $result->fetch()) {
            return static::instantiate($row);
        }
        
        return null;
    }
    
    public static function create(array $attributes): static
    {
        $record = new static($attributes);
        $record->save();
        return $record;
    }
    
    public function save(): bool
    {
        if (!$this->valid()) {
            return false;
        }
        
        $this->runCallbacks('beforeSave');
        
        if ($this->persisted) {
            $result = $this->update();
        } else {
            $result = $this->insert();
        }
        
        if ($result) {
            $this->runCallbacks('afterSave');
            $this->changes = [];
        }
        
        return $result;
    }
    
    protected function insert(): bool
    {
        $this->runCallbacks('beforeCreate');
        
        $columns = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO " . static::tableName() . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        if (static::getConnection()->execute($sql, $values)) {
            $this->attributes['id'] = static::getConnection()->lastInsertId();
            $this->persisted = true;
            $this->runCallbacks('afterCreate');
            return true;
        }
        
        return false;
    }
    
    protected function update(): bool
    {
        if (empty($this->changes)) {
            return true;
        }
        
        $this->runCallbacks('beforeUpdate');
        
        $sets = [];
        $values = [];
        
        foreach ($this->changes as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $this->attributes['id'];
        
        $sql = "UPDATE " . static::tableName() . " SET " . implode(', ', $sets) . " WHERE id = ?";
        
        if (static::getConnection()->execute($sql, $values)) {
            foreach ($this->changes as $key => $value) {
                $this->attributes[$key] = $value;
            }
            $this->runCallbacks('afterUpdate');
            return true;
        }
        
        return false;
    }
    
    public function destroy(): bool
    {
        if (!$this->persisted) {
            return false;
        }
        
        $this->runCallbacks('beforeDestroy');
        
        $sql = "DELETE FROM " . static::tableName() . " WHERE id = ?";
        
        if (static::getConnection()->execute($sql, [$this->attributes['id']])) {
            $this->persisted = false;
            $this->runCallbacks('afterDestroy');
            return true;
        }
        
        return false;
    }
    
    public static function destroyById($id): bool
    {
        $record = static::find($id);
        return $record ? $record->destroy() : false;
    }
    
    public function valid(): bool
    {
        $this->errors = [];
        $this->validate();
        return empty($this->errors);
    }
    
    protected function validate(): void
    {
        foreach (static::$validations as $field => $rules) {
            foreach ($rules as $rule => $options) {
                $this->runValidation($field, $rule, $options);
            }
        }
    }
    
    protected function runValidation(string $field, string $rule, $options): void
    {
        $value = $this->attributes[$field] ?? null;
        
        switch ($rule) {
            case 'presence':
                if ($options && empty($value)) {
                    $this->errors[$field][] = "$field can't be blank";
                }
                break;
            case 'uniqueness':
                if ($options && $this->isNotUnique($field, $value)) {
                    $this->errors[$field][] = "$field has already been taken";
                }
                break;
            case 'length':
                if (is_array($options)) {
                    if (isset($options['minimum']) && strlen($value) < $options['minimum']) {
                        $this->errors[$field][] = "$field is too short (minimum is {$options['minimum']} characters)";
                    }
                    if (isset($options['maximum']) && strlen($value) > $options['maximum']) {
                        $this->errors[$field][] = "$field is too long (maximum is {$options['maximum']} characters)";
                    }
                }
                break;
        }
    }
    
    protected function isNotUnique(string $field, $value): bool
    {
        $sql = "SELECT COUNT(*) FROM " . static::tableName() . " WHERE $field = ?";
        $params = [$value];
        
        if ($this->persisted) {
            $sql .= " AND id != ?";
            $params[] = $this->attributes['id'];
        }
        
        $result = static::getConnection()->query($sql, $params);
        return $result->fetchColumn() > 0;
    }
    
    public function errors(): array
    {
        return $this->errors;
    }
    
    protected function runCallbacks(string $type): void
    {
        if (isset(static::$callbacks[$type])) {
            foreach (static::$callbacks[$type] as $callback) {
                $this->$callback();
            }
        }
    }
    
    protected static function instantiate(array $row): static
    {
        $record = new static();
        $record->attributes = $row;
        $record->persisted = true;
        return $record;
    }
    
    protected static function tableName(): string
    {
        $class = get_called_class();
        $parts = explode('\\', $class);
        $name = end($parts);
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name)) . 's';
    }
    
    protected static function getConnection(): Connection
    {
        if (static::$connection === null) {
            static::$connection = new Connection();
        }
        return static::$connection;
    }
    
    public function toArray(): array
    {
        return $this->attributes;
    }
    
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}