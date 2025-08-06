# Tracks Framework

A Ruby on Rails-like PHP framework that follows Rails principles and conventions.

## Features

- **MVC Architecture**: Clean separation of concerns with Models, Views, and Controllers
- **ActiveRecord Pattern**: Elegant database interactions with an ORM that feels like Rails
- **RESTful Routing**: Rails-style routing with resource-based conventions
- **Database Migrations**: Version control for your database schema
- **Scaffolding**: Quick generation of models, controllers, and views
- **Interactive Console**: REPL environment for testing and debugging
- **Convention over Configuration**: Sensible defaults that just work

## Requirements

- PHP 8.0 or higher
- Composer
- SQLite, MySQL, or PostgreSQL

## Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/tracks.git
cd tracks

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Start the development server
php tracks server
```

## Quick Start

### Generate a Blog Application

```bash
# Generate a Post scaffold with title and content
php tracks generate:scaffold post title:string content:text

# Run migrations to create the database tables
php tracks db:migrate

# Start the server
php tracks server

# Visit http://localhost:3000/posts
```

## CLI Commands

### Server Commands
```bash
# Start development server (default port 3000)
php tracks server

# Start on custom port
php tracks server --port=8080
```

### Generator Commands
```bash
# Generate a model
php tracks generate:model user name:string email:string

# Generate a controller
php tracks generate:controller users

# Generate a complete scaffold
php tracks generate:scaffold product name:string price:decimal description:text
```

### Database Commands
```bash
# Run pending migrations
php tracks db:migrate

# Rollback last migration
php tracks db:rollback

# Rollback specific number of migrations
php tracks db:rollback --steps=3

# Check migration status
php tracks db:status
```

### Other Commands
```bash
# Start interactive console
php tracks console

# Display all routes
php tracks routes
```

## Project Structure

```
tracks/
├── app/
│   ├── controllers/     # Application controllers
│   ├── models/          # Application models
│   └── views/           # View templates
│       └── layouts/     # Layout templates
├── config/
│   ├── application.php  # Application configuration
│   ├── database.php     # Database configuration
│   └── routes.php       # Route definitions
├── db/
│   ├── migrate/         # Database migrations
│   └── seeds/           # Database seeds
├── lib/
│   └── tracks/          # Framework core
├── public/              # Web root
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Images
├── test/               # Test files
├── log/                # Application logs
└── tmp/                # Temporary files
```

## Routing

Define routes in `config/routes.php`:

```php
// Root route
$router->root('HomeController#index');

// RESTful resources
$router->resources('posts');
$router->resource('profile');

// Custom routes
$router->get('/about', 'PagesController#about');
$router->post('/contact', 'ContactController#create');

// Nested routes
$router->scope('/admin', function($router) {
    $router->resources('users');
    $router->resources('posts');
}, ['module' => 'Admin']);
```

## Models

Models inherit from `Tracks\ActiveRecord\Base`:

```php
<?php

namespace App\Models;

use Tracks\ActiveRecord\Base;

class Post extends Base
{
    protected static array $validations = [
        'title' => ['presence' => true, 'length' => ['minimum' => 3]],
        'content' => ['presence' => true],
    ];
    
    protected static array $callbacks = [
        'beforeSave' => ['generateSlug'],
    ];
    
    protected function generateSlug(): void
    {
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = strtolower(str_replace(' ', '-', $this->title));
        }
    }
}
```

## Controllers

Controllers inherit from `Tracks\ActionController\Base`:

```php
<?php

namespace App\Controllers;

use Tracks\ActionController\Base;
use App\Models\Post;

class PostsController extends Base
{
    protected function beforeAction(string $method, array $options = []): void
    {
        $this->authenticate();
    }
    
    public function index(): void
    {
        $this->set('posts', Post::all());
    }
    
    public function show(): void
    {
        $post = Post::find($this->param('id'));
        $this->set('post', $post);
    }
    
    public function create(): void
    {
        $post = new Post($this->requireParams('post', ['title', 'content']));
        
        if ($post->save()) {
            $this->redirectTo('/posts/' . $post->id, ['notice' => 'Post created!']);
        } else {
            $this->set('post', $post);
            $this->render('new');
        }
    }
}
```

## Views

Views use PHP templates with helpers:

```php
<h1><?= $post->title ?></h1>

<div class="content">
    <?= nl2br(htmlspecialchars($post->content)) ?>
</div>

<?= $linkTo('Edit', "/posts/{$post->id}/edit", ['class' => 'btn']) ?>

<?= $formFor($post, '/posts', ['method' => 'post']) ?>
    <div class="field">
        <label>Title</label>
        <input type="text" name="post[title]" value="<?= $post->title ?>">
    </div>
    
    <button type="submit">Save</button>
<?= $formEnd() ?>
```

## Migrations

Create migrations to manage database schema:

```php
<?php

use Tracks\ActiveRecord\Migration;

class CreatePosts extends Migration
{
    public function up(): void
    {
        $this->createTable('posts', function($t) {
            $t->string('title');
            $t->text('content');
            $t->string('slug')->index();
            $t->references('user');
            $t->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->dropTable('posts');
    }
}
```

## ActiveRecord Usage

```php
// Find records
$post = Post::find(1);
$post = Post::findBy(['slug' => 'hello-world']);
$posts = Post::all();
$posts = Post::where(['published' => true])->orderBy('created_at', 'DESC')->limit(10)->get();

// Create records
$post = Post::create(['title' => 'New Post', 'content' => 'Content here']);

// Update records
$post->title = 'Updated Title';
$post->save();

// Delete records
$post->destroy();
Post::destroy(1);

// Query builder
$posts = Post::where(['status' => 'published'])
             ->orderBy('created_at', 'DESC')
             ->limit(10)
             ->get();

// Associations (coming soon)
$user->posts();
$post->comments()->where(['approved' => true])->get();
```

## Configuration

Configure the application in `config/application.php`:

```php
return [
    'app_name' => 'My Tracks App',
    'timezone' => 'America/New_York',
    'session' => [
        'lifetime' => 120,
        'cookie' => 'tracks_session',
    ],
];
```

Configure the database in `config/database.php`:

```php
return [
    'development' => [
        'adapter' => 'mysql',
        'host' => 'localhost',
        'database' => 'tracks_dev',
        'username' => 'root',
        'password' => '',
    ],
];
```

## Testing

Run tests with PHPUnit:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/PostTest.php
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the MIT license.