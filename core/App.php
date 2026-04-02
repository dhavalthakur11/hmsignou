<?php

class App {
    protected string $controller = 'LoginController';
    protected string $method     = 'index';
    protected array  $params     = [];

    public function __construct() {
        $this->parseUrl();
    }

    private function parseUrl(): void {
        $url = $_GET['url'] ?? 'login/index';
        $url = rtrim(filter_var($url, FILTER_SANITIZE_URL), '/');
        $segments = explode('/', $url);

        // Map URL segment → controller class name (PascalCase + 'Controller')
        $controllerName = ucfirst(strtolower($segments[0])) . 'Controller';

        // Search all controller directories
        $dirs = [
            APP_PATH . '/controllers/auth/',
            APP_PATH . '/controllers/admin/',
            APP_PATH . '/controllers/modules/',
        ];

        $found = false;
        foreach ($dirs as $dir) {
            $file = $dir . $controllerName . '.php';
            if (file_exists($file)) {
                require_once $file;
                $this->controller = $controllerName;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->load404();
            return;
        }

        $controller = new $this->controller;

        // Method — defaults to 'index'
        if (isset($segments[1]) && method_exists($controller, $segments[1])) {
            $this->method = $segments[1];
        }

        // Remaining segments become parameters
        $this->params = array_slice($segments, 2);

        call_user_func_array([$controller, $this->method], $this->params);
    }

    private function load404(): void {
        http_response_code(404);
        require_once APP_PATH . '/views/error/error.php';
    }
}