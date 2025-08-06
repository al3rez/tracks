<?php

namespace Tracks\Generators;

class ModelGenerator extends Generator
{
    public function generate(): void
    {
        $className = $this->camelCase($this->name);
        $tableName = $this->snakeCase($this->pluralize($this->name));
        
        $content = "<?php\n\n";
        $content .= "namespace App\\Models;\n\n";
        $content .= "use Tracks\\ActiveRecord\\Base;\n\n";
        $content .= "class $className extends Base\n";
        $content .= "{\n";
        
        if (isset($this->options['attributes'])) {
            $content .= $this->generateValidations();
        }
        
        $content .= "}";
        
        $this->createFile("app/models/$className.php", $content);
        
        if ($this->options['migration'] ?? true) {
            $this->generateMigration($tableName);
        }
    }
    
    private function generateValidations(): string
    {
        $validations = "    protected static array \$validations = [\n";
        
        foreach ($this->options['attributes'] as $attr => $type) {
            if ($type === 'string' || $type === 'text') {
                $validations .= "        '$attr' => ['presence' => true],\n";
            }
        }
        
        $validations .= "    ];\n\n";
        
        return $validations;
    }
    
    private function generateMigration(string $tableName): void
    {
        $migrationName = 'create_' . $tableName;
        $timestamp = date('YmdHis');
        $filename = $timestamp . '_' . $migrationName . '.php';
        $className = 'Create' . $this->camelCase($tableName);
        
        $content = "<?php\n\n";
        $content .= "use Tracks\\ActiveRecord\\Migration;\n\n";
        $content .= "class $className extends Migration\n";
        $content .= "{\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        $content .= "        \$this->createTable('$tableName', function(\$t) {\n";
        
        if (isset($this->options['attributes'])) {
            foreach ($this->options['attributes'] as $attr => $type) {
                $content .= "            \$t->$type('$attr');\n";
            }
        }
        
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        $content .= "        \$this->dropTable('$tableName');\n";
        $content .= "    }\n";
        $content .= "}";
        
        $this->createFile("db/migrate/$filename", $content);
    }
}