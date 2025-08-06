<?php

namespace Tracks;

use Tracks\Routing\Router;
use Tracks\ActionController\Base as Controller;

class Application
{
    private static ?Application $instance = null;
    private Router $router;
    private array $config = [];
    private string $rootPath;
    private string $environment;
    
    private function __construct()
    {
        $this->rootPath = dirname(__DIR__, 2);
        $this->environment = $_ENV['TRACKS_ENV'] ?? 'development';
        $this->router = new Router();
        $this->loadConfig();
        $this->loadRoutes();
    }
    
    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $route = $this->router->match($method, $path);
        
        if ($route) {
            $this->dispatch($route);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
    
    private function dispatch(array $route): void
    {
        [$controllerName, $action] = explode('#', $route['controller']);
        $controllerClass = "App\\Controllers\\" . $controllerName;
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }
        
        $controller = new $controllerClass();
        $controller->setParams($route['params']);
        $controller->setCurrentAction($action);
        
        if (!method_exists($controller, $action)) {
            throw new \Exception("Action {$action} not found in {$controllerClass}");
        }
        
        $controller->runFilters($action);
        $controller->$action();
        
        // Only auto-render if the controller hasn't already rendered
        if (!$controller->hasRendered()) {
            $controller->render();
        }
    }
    
    private function loadConfig(): void
    {
        $configFile = $this->rootPath . '/config/application.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    private function loadRoutes(): void
    {
        $routesFile = $this->rootPath . '/config/routes.php';
        if (file_exists($routesFile)) {
            $router = $this->router;
            require $routesFile;
        }
    }
    
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
    
    public function getEnvironment(): string
    {
        return $this->environment;
    }
    
    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}