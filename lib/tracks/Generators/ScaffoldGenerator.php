<?php

namespace Tracks\Generators;

class ScaffoldGenerator extends Generator
{
    public function generate(): void
    {
        $modelGenerator = new ModelGenerator($this->name, $this->options);
        $modelGenerator->generate();
        
        $controllerGenerator = new ControllerGenerator($this->name, $this->options);
        $controllerGenerator->generate();
        
        $this->generateViews();
        $this->addRoutes();
    }
    
    private function generateViews(): void
    {
        $viewPath = $this->snakeCase($this->pluralize($this->name));
        $modelName = $this->camelCase($this->singularize($this->name));
        $varName = $this->snakeCase($this->singularize($this->name));
        $varNamePlural = $this->snakeCase($this->pluralize($this->name));
        
        $this->generateIndexView($viewPath, $varNamePlural, $varName);
        $this->generateShowView($viewPath, $varName);
        $this->generateNewView($viewPath, $varName, $modelName);
        $this->generateEditView($viewPath, $varName, $modelName);
        $this->generateFormPartial($viewPath, $varName);
    }
    
    private function generateIndexView(string $path, string $varNamePlural, string $varName): void
    {
        $content = <<<PHP
<h1>Listing $varNamePlural</h1>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th colspan="3">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (\$$varNamePlural as \$$varName): ?>
      <tr>
        <td><?= \${$varName}->id ?></td>
        <td><?= \${$varName}->name ?></td>
        <td><?= \$linkTo('Show', "/$path/{\${$varName}->id}") ?></td>
        <td><?= \$linkTo('Edit', "/$path/{\${$varName}->id}/edit") ?></td>
        <td>
          <form action="/$path/<?= \${$varName}->id ?>" method="post" style="display:inline">
            <input type="hidden" name="_method" value="delete">
            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<br>

<?= \$linkTo('New $varName', "/$path/new") ?>
PHP;
        
        $this->createFile("app/views/$path/index.php", $content);
    }
    
    private function generateShowView(string $path, string $varName): void
    {
        $content = <<<PHP
<h1><?= \${$varName}->name ?></h1>

<p>
  <strong>Description:</strong>
  <?= \${$varName}->description ?>
</p>

<p>
  <?= \$linkTo('Edit', "/$path/{\${$varName}->id}/edit") ?> |
  <?= \$linkTo('Back', "/$path") ?>
</p>
PHP;
        
        $this->createFile("app/views/$path/show.php", $content);
    }
    
    private function generateNewView(string $path, string $varName, string $modelName): void
    {
        $content = <<<PHP
<h1>New $modelName</h1>

<?= \$renderPartial('$path/form', ['$varName' => \$$varName]) ?>

<?= \$linkTo('Back', "/$path") ?>
PHP;
        
        $this->createFile("app/views/$path/new.php", $content);
    }
    
    private function generateEditView(string $path, string $varName, string $modelName): void
    {
        $content = <<<PHP
<h1>Editing $modelName</h1>

<?= \$renderPartial('$path/form', ['$varName' => \$$varName]) ?>

<?= \$linkTo('Show', "/$path/{\${$varName}->id}") ?> |
<?= \$linkTo('Back', "/$path") ?>
PHP;
        
        $this->createFile("app/views/$path/edit.php", $content);
    }
    
    private function generateFormPartial(string $path, string $varName): void
    {
        $formPath = \$$varName->id ? "/$path/{\${$varName}->id}" : "/$path";
        $method = \$$varName->id ? 'patch' : 'post';
        
        $content = <<<PHP
<?php if (!empty(\${$varName}->errors())): ?>
  <div class="error">
    <h3>Errors:</h3>
    <ul>
      <?php foreach (\${$varName}->errors() as \$field => \$errors): ?>
        <?php foreach (\$errors as \$error): ?>
          <li><?= \$error ?></li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="<?= \$formPath ?>" method="post">
  <?php if ('$method' === 'patch'): ?>
    <input type="hidden" name="_method" value="patch">
  <?php endif; ?>
  
  <div class="field">
    <label for="{$varName}_name">Name</label>
    <input type="text" id="{$varName}_name" name="{$varName}[name]" value="<?= \${$varName}->name ?>">
  </div>
  
  <div class="field">
    <label for="{$varName}_description">Description</label>
    <textarea id="{$varName}_description" name="{$varName}[description]"><?= \${$varName}->description ?></textarea>
  </div>
  
  <div class="actions">
    <button type="submit">Save</button>
  </div>
</form>
PHP;
        
        $this->createFile("app/views/$path/_form.php", $content);
    }
    
    private function addRoutes(): void
    {
        $resourceName = $this->snakeCase($this->pluralize($this->name));
        $routeCode = "\$router->resources('$resourceName');";
        
        $this->appendToFile('config/routes.php', $routeCode);
    }
}