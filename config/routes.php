<?php

use Tracks\Routing\Router;

$router->root('PostsController#index');

$router->resources('posts');
$router->resources('comments');

// Nested routes for comments under posts
$router->scope('/posts/:post_id', function(Router $router) {
    $router->resources('comments');
});