<?php

namespace Tracks\Generators;

class AppGenerator extends Generator
{
    public function generate(): void
    {
        $appPath = $this->rootPath . '/../' . $this->name;
        
        if (is_dir($appPath)) {
            echo "Directory {$this->name} already exists!\n";
            return;
        }
        
        echo "Creating new Tracks application: {$this->name}\n";
        
        // Create directory structure
        $this->createDirectories($appPath);
        
        // Copy framework files
        $this->copyFrameworkFiles($appPath);
        
        // Create default files
        $this->createDefaultFiles($appPath);
        
        echo "\n✅ Application created successfully!\n\n";
        echo "Next steps:\n";
        echo "  cd {$this->name}\n";
        echo "  composer install\n";
        echo "  php tracks server\n\n";
    }
    
    private function createDirectories(string $appPath): void
    {
        $dirs = [
            'app/Controllers',
            'app/Models', 
            'app/Views/layouts',
            'app/Views/shared',
            'config',
            'db/migrate',
            'db/seeds',
            'lib/tasks',
            'public/css',
            'public/js',
            'public/images',
            'test/controllers',
            'test/models',
            'test/fixtures',
            'vendor',
            'tmp/cache',
            'log',
        ];
        
        foreach ($dirs as $dir) {
            mkdir($appPath . '/' . $dir, 0755, true);
            echo "  create  $dir\n";
        }
    }
    
    private function copyFrameworkFiles(string $appPath): void
    {
        // Copy the framework lib
        $this->copyDirectory($this->rootPath . '/lib', $appPath . '/lib');
        
        // Copy tracks CLI
        copy($this->rootPath . '/tracks', $appPath . '/tracks');
        chmod($appPath . '/tracks', 0755);
        echo "  create  tracks\n";
    }
    
    private function createDefaultFiles(string $appPath): void
    {
        // composer.json
        $this->writeFile($appPath . '/composer.json', $this->composerJson());
        
        // .env.example
        $this->writeFile($appPath . '/.env.example', $this->envExample());
        
        // .gitignore
        $this->writeFile($appPath . '/.gitignore', $this->gitignore());
        
        // public/index.php
        $this->writeFile($appPath . '/public/index.php', $this->publicIndex());
        
        // public/.htaccess
        $this->writeFile($appPath . '/public/.htaccess', $this->htaccess());
        
        // config files
        $this->writeFile($appPath . '/config/application.php', $this->configApplication());
        $this->writeFile($appPath . '/config/database.php', $this->configDatabase());
        $this->writeFile($appPath . '/config/routes.php', $this->configRoutes());
        $this->writeFile($appPath . '/config/boot.php', $this->configBoot());
        
        // Default controller
        $this->writeFile($appPath . '/app/Controllers/HomeController.php', $this->homeController());
        
        // Default views
        $this->writeFile($appPath . '/app/Views/layouts/application.php', $this->layoutApplication());
        $this->writeFile($appPath . '/app/Views/home/index.php', $this->homeIndex());
        
        // README
        $this->writeFile($appPath . '/README.md', $this->readme());
    }
    
    private function copyDirectory(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        
        closedir($dir);
    }
    
    private function writeFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
        echo "  create  " . str_replace(dirname($path) . '/', '', $path) . "\n";
    }
    
    private function composerJson(): string
    {
        $appName = $this->snakeCase($this->name);
        return <<<JSON
{
    "name": "$appName/application",
    "description": "A Tracks application",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "vlucas/phpdotenv": "^5.5",
        "symfony/console": "^6.0"
    },
    "autoload": {
        "psr-4": {
            "Tracks\\\\": "lib/tracks/",
            "App\\\\": "app/"
        },
        "files": [
            "lib/tracks/helpers.php"
        ]
    }
}
JSON;
    }
    
    private function envExample(): string
    {
        return <<<ENV
TRACKS_ENV=development

DB_HOST=localhost
DB_PORT=3306
DB_NAME={$this->snakeCase($this->name)}_development
DB_USER=root
DB_PASS=
ENV;
    }
    
    private function gitignore(): string
    {
        return <<<GITIGNORE
/vendor/
/.env
/log/*.log
/tmp/*
/db/*.sqlite3
.DS_Store
*.swp
*~
GITIGNORE;
    }
    
    private function publicIndex(): string
    {
        return <<<PHP
<?php

require_once dirname(__DIR__) . '/config/boot.php';

use Tracks\Application;

\$app = Application::getInstance();
\$app->run();
PHP;
    }
    
    private function htaccess(): string
    {
        return <<<HTACCESS
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
HTACCESS;
    }
    
    private function configApplication(): string
    {
        return file_get_contents($this->rootPath . '/config/application.php');
    }
    
    private function configDatabase(): string
    {
        return file_get_contents($this->rootPath . '/config/database.php');
    }
    
    private function configRoutes(): string
    {
        return <<<'PHP'
<?php

use Tracks\Routing\Router;

$router->root('HomeController#index');

// Define your routes here
// $router->resources('posts');
// $router->get('/about', 'PagesController#about');
PHP;
    }
    
    private function configBoot(): string
    {
        return file_get_contents($this->rootPath . '/config/boot.php');
    }
    
    private function homeController(): string
    {
        return <<<'PHP'
<?php

namespace App\Controllers;

use Tracks\ActionController\Base;

class HomeController extends Base
{
    public function index(): void
    {
        $this->set('message', 'Welcome to Tracks!');
    }
}
PHP;
    }
    
    private function layoutApplication(): string
    {
        return file_get_contents($this->rootPath . '/app/Views/layouts/application.php');
    }
    
    private function homeIndex(): string
    {
        return <<<'PHP'
<div style="text-align: center; padding: 5rem 0;">
    <h1 style="font-size: 3rem; color: #c52f24;">
        <?= $message ?>
    </h1>
    <p style="font-size: 1.25rem; color: #666;">
        Your new Tracks application is ready!
    </p>
</div>
PHP;
    }
    
    private function readme(): string
    {
        $appName = $this->camelCase($this->name);
        return <<<MD
# $appName

A Tracks application.

## Setup

```bash
composer install
cp .env.example .env
php tracks db:migrate
```

## Running

```bash
php tracks server
```

Visit http://localhost:3000

## Generators

```bash
# Generate a scaffold
php tracks generate:scaffold post title:string content:text

# Generate a model
php tracks generate:model user name:string email:string

# Generate a controller
php tracks generate:controller posts
```

## Database

```bash
# Run migrations
php tracks db:migrate

# Rollback
php tracks db:rollback

# Check status
php tracks db:status
```
MD;
    }
}