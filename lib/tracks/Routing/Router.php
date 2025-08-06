<?php

namespace Tracks\Routing;

class Router
{
    private array $routes = [];
    private ?string $namespace = null;
    private array $currentScope = [];
    
    public function get(string $path, string $controller, array $options = []): void
    {
        $this->addRoute('GET', $path, $controller, $options);
    }
    
    public function post(string $path, string $controller, array $options = []): void
    {
        $this->addRoute('POST', $path, $controller, $options);
    }
    
    public function put(string $path, string $controller, array $options = []): void
    {
        $this->addRoute('PUT', $path, $controller, $options);
    }
    
    public function patch(string $path, string $controller, array $options = []): void
    {
        $this->addRoute('PATCH', $path, $controller, $options);
    }
    
    public function delete(string $path, string $controller, array $options = []): void
    {
        $this->addRoute('DELETE', $path, $controller, $options);
    }
    
    public function resources(string $name, array $options = []): void
    {
        $controller = ucfirst($name) . 'Controller';
        $path = '/' . $name;
        
        $this->get($path, $controller . '#index', ['as' => $name]);
        $this->get($path . '/new', $controller . '#new', ['as' => 'new_' . rtrim($name, 's')]);
        $this->post($path, $controller . '#create');
        $this->get($path . '/:id', $controller . '#show', ['as' => rtrim($name, 's')]);
        $this->get($path . '/:id/edit', $controller . '#edit', ['as' => 'edit_' . rtrim($name, 's')]);
        $this->patch($path . '/:id', $controller . '#update');
        $this->put($path . '/:id', $controller . '#update');
        $this->delete($path . '/:id', $controller . '#destroy');
    }
    
    public function resource(string $name, array $options = []): void
    {
        $controller = ucfirst($name) . 'Controller';
        $path = '/' . $name;
        
        $this->get($path . '/new', $controller . '#new', ['as' => 'new_' . $name]);
        $this->post($path, $controller . '#create');
        $this->get($path, $controller . '#show', ['as' => $name]);
        $this->get($path . '/edit', $controller . '#edit', ['as' => 'edit_' . $name]);
        $this->patch($path, $controller . '#update');
        $this->put($path, $controller . '#update');
        $this->delete($path, $controller . '#destroy');
    }
    
    public function namespace(string $namespace, callable $callback): void
    {
        $previousNamespace = $this->namespace;
        $this->namespace = $namespace;
        $callback($this);
        $this->namespace = $previousNamespace;
    }
    
    public function scope(string $path, callable $callback, array $options = []): void
    {
        $previousScope = $this->currentScope;
        $this->currentScope[] = $path;
        
        if (isset($options['module'])) {
            $this->namespace($options['module'], $callback);
        } else {
            $callback($this);
        }
        
        $this->currentScope = $previousScope;
    }
    
    public function root(string $controller): void
    {
        $this->get('/', $controller, ['as' => 'root']);
    }
    
    private function addRoute(string $method, string $path, string $controller, array $options): void
    {
        if (!empty($this->currentScope)) {
            $path = implode('', $this->currentScope) . $path;
        }
        
        if ($this->namespace) {
            $controller = $this->namespace . '\\' . $controller;
        }
        
        $pattern = $this->convertPathToRegex($path);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'name' => $options['as'] ?? null,
            'options' => $options
        ];
    }
    
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/:[a-zA-Z_]+/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                
                $params = [];
                if (preg_match_all('/:([a-zA-Z_]+)/', $route['path'], $paramNames)) {
                    foreach ($paramNames[1] as $index => $name) {
                        $params[$name] = $matches[$index] ?? null;
                    }
                }
                
                return [
                    'controller' => $route['controller'],
                    'params' => $params,
                    'route' => $route
                ];
            }
        }
        
        return null;
    }
    
    public function pathFor(string $name, array $params = []): ?string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $path = $route['path'];
                foreach ($params as $key => $value) {
                    $path = str_replace(':' . $key, $value, $path);
                }
                return $path;
            }
        }
        return null;
    }
}