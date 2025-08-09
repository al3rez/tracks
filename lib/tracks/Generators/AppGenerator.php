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
            'app/Views/welcome',
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
        $this->writeFile($appPath . '/app/Controllers/WelcomeController.php', $this->welcomeController());
        
        // Default views
        $this->writeFile($appPath . '/app/Views/layouts/application.php', $this->layoutApplication());
        $this->writeFile($appPath . '/app/Views/welcome/index.php', $this->welcomeIndex());
        
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

$router->root('WelcomeController#index');

// Define your routes here
// $router->resources('posts');
// $router->get('/about', 'PagesController#about');
PHP;
    }
    
    private function configBoot(): string
    {
        return file_get_contents($this->rootPath . '/config/boot.php');
    }
    
    private function welcomeController(): string
    {
        return <<<'PHP'
<?php

namespace App\Controllers;

use Tracks\ActionController\Base;

class WelcomeController extends Base
{
    public function index(): void
    {
        // Rails-like welcome page
    }
}
PHP;
    }
    
    private function layoutApplication(): string
    {
        return file_get_contents($this->rootPath . '/app/Views/layouts/application.php');
    }
    
    private function welcomeIndex(): string
    {
        return <<<'PHP'
<style>
    .welcome-hero {
        text-align: center;
        padding: 3rem 0;
    }
    
    .welcome-hero h1 {
        font-size: 4rem;
        color: #c52f24;
        margin-bottom: 1rem;
        font-weight: 300;
    }
    
    .welcome-hero .tagline {
        font-size: 1.5rem;
        color: #666;
        margin-bottom: 3rem;
    }
    
    .getting-started {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 2rem;
        margin: 2rem auto;
        max-width: 800px;
    }
    
    .getting-started h2 {
        color: #333;
        margin-bottom: 1rem;
    }
    
    .getting-started code {
        background: #e9ecef;
        padding: 3px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        color: #d73502;
    }
    
    .command-box {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 1rem;
        border-radius: 4px;
        margin: 1rem 0;
        font-family: 'Courier New', monospace;
        overflow-x: auto;
    }
    
    .command-box .comment {
        color: #75715e;
    }
    
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .feature {
        text-align: center;
        padding: 1.5rem;
    }
    
    .feature h3 {
        color: #c52f24;
        margin-bottom: 0.5rem;
    }
    
    .feature p {
        color: #666;
        line-height: 1.6;
    }
</style>

<div class="welcome-hero">
    <h1>Welcome to Tracks!</h1>
    <p class="tagline">Your Rails-like PHP Framework</p>
</div>

<div class="getting-started">
    <h2>🚀 Getting Started</h2>
    
    <p>Your Tracks application is up and running! Here's how to start building:</p>
    
    <h3>1. Generate a scaffold</h3>
    <div class="command-box">
        <span class="comment"># Generate a complete resource with model, controller, and views</span><br>
        $ php tracks generate:scaffold post title:string content:text published:boolean
    </div>
    
    <h3>2. Run migrations</h3>
    <div class="command-box">
        <span class="comment"># Create the database tables</span><br>
        $ php tracks db:migrate
    </div>
    
    <h3>3. Start coding!</h3>
    <p>Your scaffold is ready at <code>/posts</code></p>
</div>

<div class="features">
    <div class="feature">
        <h3>MVC Architecture</h3>
        <p>Clean separation of concerns with Models, Views, and Controllers</p>
    </div>
    
    <div class="feature">
        <h3>Active Record ORM</h3>
        <p>Elegant database interactions with a Rails-like Active Record pattern</p>
    </div>
    
    <div class="feature">
        <h3>Routing</h3>
        <p>RESTful routing with resource helpers and custom route definitions</p>
    </div>
    
    <div class="feature">
        <h3>Generators</h3>
        <p>Scaffold, model, and controller generators to speed up development</p>
    </div>
</div>

<div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
    <p style="color: #999;">
        Tracks Framework v1.0.0 | 
        <a href="https://github.com/tracks/tracks" style="color: #c52f24;">Documentation</a> | 
        <a href="https://github.com/tracks/tracks" style="color: #c52f24;">GitHub</a>
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