<?php

namespace Tracks\Generators;

class ControllerGenerator extends Generator
{
    public function generate(): void
    {
        $className = $this->camelCase($this->name) . 'Controller';
        $modelName = $this->camelCase($this->singularize($this->name));
        $varName = $this->snakeCase($this->singularize($this->name));
        $varNamePlural = $this->snakeCase($this->pluralize($this->name));
        
        $actions = $this->options['actions'] ?? ['index', 'show', 'new', 'create', 'edit', 'update', 'destroy'];
        
        $content = "<?php\n\n";
        $content .= "namespace App\\Controllers;\n\n";
        $content .= "use Tracks\\ActionController\\Base;\n";
        $content .= "use App\\Models\\$modelName;\n\n";
        $content .= "class $className extends Base\n";
        $content .= "{\n";
        
        if (in_array('index', $actions)) {
            $content .= $this->generateIndexAction($varNamePlural, $modelName);
        }
        
        if (in_array('show', $actions)) {
            $content .= $this->generateShowAction($varName, $modelName);
        }
        
        if (in_array('new', $actions)) {
            $content .= $this->generateNewAction($varName, $modelName);
        }
        
        if (in_array('create', $actions)) {
            $content .= $this->generateCreateAction($varName, $modelName, $varNamePlural);
        }
        
        if (in_array('edit', $actions)) {
            $content .= $this->generateEditAction($varName, $modelName);
        }
        
        if (in_array('update', $actions)) {
            $content .= $this->generateUpdateAction($varName, $modelName, $varNamePlural);
        }
        
        if (in_array('destroy', $actions)) {
            $content .= $this->generateDestroyAction($varName, $modelName, $varNamePlural);
        }
        
        $content .= $this->generatePrivateMethods($varName, $modelName);
        
        $content .= "}";
        
        $this->createFile("app/controllers/$className.php", $content);
    }
    
    private function generateIndexAction(string $varNamePlural, string $modelName): string
    {
        return <<<PHP
    public function index(): void
    {
        \$this->set('$varNamePlural', $modelName::all());
    }
    
PHP;
    }
    
    private function generateShowAction(string $varName, string $modelName): string
    {
        return <<<PHP
    public function show(): void
    {
        \$this->set('$varName', \$this->find$modelName());
    }
    
PHP;
    }
    
    private function generateNewAction(string $varName, string $modelName): string
    {
        return <<<PHP
    public function new(): void
    {
        \$this->set('$varName', new $modelName());
    }
    
PHP;
    }
    
    private function generateCreateAction(string $varName, string $modelName, string $varNamePlural): string
    {
        return <<<PHP
    public function create(): void
    {
        \$$varName = new $modelName(\$this->{$varName}Params());
        
        if (\${$varName}->save()) {
            \$this->redirectTo('/$varNamePlural/' . \${$varName}->id, ['notice' => '$modelName was successfully created.']);
        } else {
            \$this->set('$varName', \$$varName);
            \$this->render('new');
        }
    }
    
PHP;
    }
    
    private function generateEditAction(string $varName, string $modelName): string
    {
        return <<<PHP
    public function edit(): void
    {
        \$this->set('$varName', \$this->find$modelName());
    }
    
PHP;
    }
    
    private function generateUpdateAction(string $varName, string $modelName, string $varNamePlural): string
    {
        return <<<PHP
    public function update(): void
    {
        \$$varName = \$this->find$modelName();
        \${$varName}->fill(\$this->{$varName}Params());
        
        if (\${$varName}->save()) {
            \$this->redirectTo('/$varNamePlural/' . \${$varName}->id, ['notice' => '$modelName was successfully updated.']);
        } else {
            \$this->set('$varName', \$$varName);
            \$this->render('edit');
        }
    }
    
PHP;
    }
    
    private function generateDestroyAction(string $varName, string $modelName, string $varNamePlural): string
    {
        return <<<PHP
    public function destroy(): void
    {
        \$$varName = \$this->find$modelName();
        \${$varName}->destroy();
        \$this->redirectTo('/$varNamePlural', ['notice' => '$modelName was successfully destroyed.']);
    }
    
PHP;
    }
    
    private function generatePrivateMethods(string $varName, string $modelName): string
    {
        return <<<PHP
    private function find$modelName(): $modelName
    {
        \$$varName = $modelName::find(\$this->param('id'));
        
        if (!\$$varName) {
            throw new \Exception('$modelName not found');
        }
        
        return \$$varName;
    }
    
    private function {$varName}Params(): array
    {
        return \$this->requireParams('$varName', ['name', 'description']);
    }
    
PHP;
    }
}