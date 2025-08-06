<?php

namespace Tracks\ActionView;

class Renderer
{
    private string $viewsPath;
    private string $layoutsPath;
    
    public function __construct()
    {
        $rootPath = dirname(__DIR__, 3);
        $this->viewsPath = $rootPath . '/app/Views/';
        $this->layoutsPath = $rootPath . '/app/Views/layouts/';
    }
    
    public function render(string $view, array $variables = [], ?string $layout = null): string
    {
        $viewContent = $this->renderView($view, $variables);
        
        if ($layout !== null && $layout !== false) {
            $layoutFile = $this->layoutsPath . $layout . '.php';
            if (file_exists($layoutFile)) {
                $variables['yield'] = $viewContent;
                return $this->renderFile($layoutFile, $variables);
            }
        }
        
        return $viewContent;
    }
    
    public function renderPartial(string $partial, array $variables = []): string
    {
        $parts = explode('/', $partial);
        $filename = array_pop($parts);
        $filename = '_' . $filename;
        $parts[] = $filename;
        $partial = implode('/', $parts);
        
        return $this->renderView($partial, $variables);
    }
    
    private function renderView(string $view, array $variables): string
    {
        $viewFile = $this->viewsPath . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }
        
        return $this->renderFile($viewFile, $variables);
    }
    
    private function renderFile(string $file, array $variables): string
    {
        extract($variables);
        
        $renderPartial = function($partial, $locals = []) use ($variables) {
            $renderer = new self();
            return $renderer->renderPartial($partial, array_merge($variables, $locals));
        };
        
        ob_start();
        include $file;
        return ob_get_clean();
    }
}