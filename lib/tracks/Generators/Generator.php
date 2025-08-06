<?php

namespace Tracks\Generators;

abstract class Generator
{
    protected string $name;
    protected array $options;
    protected string $rootPath;
    
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->rootPath = dirname(__DIR__, 3);
    }
    
    abstract public function generate(): void;
    
    protected function createFile(string $path, string $content): void
    {
        $fullPath = $this->rootPath . '/' . $path;
        $directory = dirname($fullPath);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        if (file_exists($fullPath) && !($this->options['force'] ?? false)) {
            echo "File already exists: $path (use --force to overwrite)\n";
            return;
        }
        
        file_put_contents($fullPath, $content);
        echo "Created: $path\n";
    }
    
    protected function appendToFile(string $path, string $content): void
    {
        $fullPath = $this->rootPath . '/' . $path;
        
        if (!file_exists($fullPath)) {
            echo "File does not exist: $path\n";
            return;
        }
        
        $existing = file_get_contents($fullPath);
        if (strpos($existing, $content) === false) {
            file_put_contents($fullPath, $existing . "\n" . $content);
            echo "Updated: $path\n";
        }
    }
    
    protected function camelCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }
    
    protected function snakeCase(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }
    
    protected function pluralize(string $word): string
    {
        $rules = [
            '/(s)$/i' => '$1',
            '/(x|ch|sh|ss)$/i' => '$1es',
            '/([^aeiou])y$/i' => '$1ies',
            '/(o)$/i' => '$1es',
            '/$/i' => 's'
        ];
        
        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $word)) {
                return preg_replace($pattern, $replacement, $word);
            }
        }
        
        return $word . 's';
    }
    
    protected function singularize(string $word): string
    {
        $rules = [
            '/(s)es$/i' => '$1',
            '/(x|ch|sh|ss)es$/i' => '$1',
            '/([^aeiou])ies$/i' => '$1y',
            '/(o)es$/i' => '$1',
            '/s$/i' => ''
        ];
        
        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $word)) {
                return preg_replace($pattern, $replacement, $word);
            }
        }
        
        return $word;
    }
}