<?php

namespace Tracks\ActionController;

use Tracks\ActionView\Renderer;

abstract class Base
{
    protected array $params = [];
    protected array $variables = [];
    protected ?string $layout = 'application';
    protected bool $rendered = false;
    protected ?string $currentAction = null;
    protected array $headers = [];
    protected int $statusCode = 200;
    protected ?string $redirectTo = null;
    protected array $flash = [];
    protected array $beforeActions = [];
    protected array $afterActions = [];
    protected array $skipBeforeActions = [];
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
    }
    
    public function setParams(array $params): void
    {
        $this->params = array_merge($params, $_GET, $_POST);
    }
    
    public function setCurrentAction(string $action): void
    {
        $this->currentAction = $action;
    }
    
    protected function beforeAction(string $method, array $options = []): void
    {
        $this->beforeActions[] = ['method' => $method, 'options' => $options];
    }
    
    protected function afterAction(string $method, array $options = []): void
    {
        $this->afterActions[] = ['method' => $method, 'options' => $options];
    }
    
    protected function skipBeforeAction(string $method, array $options = []): void
    {
        $this->skipBeforeActions[] = ['method' => $method, 'options' => $options];
    }
    
    public function runFilters(string $action): void
    {
        foreach ($this->beforeActions as $filter) {
            if ($this->shouldRunFilter($filter, $action) && 
                !$this->shouldSkipFilter($filter['method'], $action)) {
                $this->{$filter['method']}();
            }
        }
    }
    
    private function shouldRunFilter(array $filter, string $action): bool
    {
        if (empty($filter['options'])) {
            return true;
        }
        
        if (isset($filter['options']['only'])) {
            return in_array($action, (array)$filter['options']['only']);
        }
        
        if (isset($filter['options']['except'])) {
            return !in_array($action, (array)$filter['options']['except']);
        }
        
        return true;
    }
    
    private function shouldSkipFilter(string $method, string $action): bool
    {
        foreach ($this->skipBeforeActions as $skip) {
            if ($skip['method'] === $method) {
                return $this->shouldRunFilter($skip, $action);
            }
        }
        return false;
    }
    
    public function render(?string $view = null, array $options = []): void
    {
        if ($this->rendered) {
            return;
        }
        
        $this->rendered = true;
        
        if ($view === null) {
            $controller = $this->getControllerName();
            $action = $this->getActionName();
            $view = $controller . '/' . $action;
        }
        
        if (isset($options['json'])) {
            $this->renderJson($options['json']);
            return;
        }
        
        if (isset($options['text'])) {
            $this->renderText($options['text']);
            return;
        }
        
        if (isset($options['status'])) {
            $this->statusCode = $options['status'];
        }
        
        $layout = $options['layout'] ?? $this->layout;
        
        $renderer = new Renderer();
        $content = $renderer->render($view, array_merge($this->variables, $this->getHelperMethods()), $layout);
        
        $this->sendHeaders();
        echo $content;
    }
    
    protected function renderJson($data): void
    {
        $this->headers['Content-Type'] = 'application/json';
        $this->sendHeaders();
        echo json_encode($data);
    }
    
    protected function renderText(string $text): void
    {
        $this->headers['Content-Type'] = 'text/plain';
        $this->sendHeaders();
        echo $text;
    }
    
    protected function redirectTo(string $path, array $options = []): void
    {
        if (isset($options['notice'])) {
            $_SESSION['flash']['notice'] = $options['notice'];
        }
        
        if (isset($options['alert'])) {
            $_SESSION['flash']['alert'] = $options['alert'];
        }
        
        $this->headers['Location'] = $path;
        $this->statusCode = $options['status'] ?? 302;
        $this->rendered = true;
        $this->sendHeaders();
    }
    
    protected function redirectBack(array $options = []): void
    {
        $fallback = $options['fallback_location'] ?? '/';
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirectTo($referer, $options);
    }
    
    private function sendHeaders(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }
    
    protected function set(string $name, $value): void
    {
        $this->variables[$name] = $value;
    }
    
    public function hasRendered(): bool
    {
        return $this->rendered;
    }
    
    protected function params(): array
    {
        return $this->params;
    }
    
    protected function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    protected function requireParams(string $key, array $permitted): array
    {
        $data = $this->params[$key] ?? [];
        return array_intersect_key($data, array_flip($permitted));
    }
    
    private function getControllerName(): string
    {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $name = end($parts);
        return strtolower(str_replace('Controller', '', $name));
    }
    
    private function getActionName(): string
    {
        return $this->currentAction ?? 'index';
    }
    
    private function getHelperMethods(): array
    {
        return [
            'flash' => $this->flash,
            'params' => $this->params,
            'linkTo' => function($text, $path, $options = []) {
                $attrs = '';
                foreach ($options as $key => $value) {
                    $attrs .= " $key=\"$value\"";
                }
                return "<a href=\"$path\"$attrs>$text</a>";
            },
            'formFor' => function($model, $url, $options = []) {
                $method = $options['method'] ?? 'post';
                $html = "<form action=\"$url\" method=\"post\">";
                if ($method !== 'post') {
                    $html .= "<input type=\"hidden\" name=\"_method\" value=\"$method\">";
                }
                return $html;
            },
            'formEnd' => function() {
                return '</form>';
            }
        ];
    }
}