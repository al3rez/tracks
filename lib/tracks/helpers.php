<?php

function app(): \Tracks\Application
{
    return \Tracks\Application::getInstance();
}

function router(): \Tracks\Routing\Router
{
    return app()->getRouter();
}

function rootPath(): string
{
    return app()->getRootPath();
}

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

function config(string $key, $default = null)
{
    return app()->config($key, $default);
}

function dd(...$vars): void
{
    foreach ($vars as $var) {
        var_dump($var);
    }
    die();
}

function dump(...$vars): void
{
    foreach ($vars as $var) {
        var_dump($var);
    }
}

function pathFor(string $name, array $params = []): ?string
{
    return router()->pathFor($name, $params);
}

function redirect(string $path, array $options = []): void
{
    $status = $options['status'] ?? 302;
    header("Location: $path", true, $status);
    exit;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfTokenField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrfToken(): bool
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }
    
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}