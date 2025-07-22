<?php
//HELPERS
require_once "helpers/Response.php";

class Router {
    private $routes = [];
    
    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                call_user_func_array($route['handler'], $params);
                return;
            }
        }
        
        Response::error('Ruta no encontrada', 404);
    }
    
    private function matchPath($routePath, $requestPath) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        preg_match($pattern, $requestPath, $matches);
        return array_slice($matches, 1);
    }
}

?>