<?php

namespace Routes;

class Router
{
    private $current_path;
    private $routes = [];
    private $route_params = [];
    public function __construct($baseUrl = "api/v1")
    {
        $allowedOrigins = [
            "http://localhost:3000",
            "http://localhost:3001",
            "https://eventx-mega.vercel.app",
            "https://eventx.dimaljay.com"
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Credentials: true");
            header("Vary: Origin");
        }

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json; charset=UTF-8");

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsed_url = parse_url($request_uri, PHP_URL_PATH);
        $baseUrl = '/' . trim($baseUrl, '/');

        $url_segments = explode($baseUrl, $parsed_url);
        $raw_path = isset($url_segments[1]) ? $url_segments[1] : '';
        $this->current_path = '/' . trim($raw_path, '/');
    }

    public function get($path, $controllerAction, $middlewares = [])
    {
        $this->routes['GET'][$path] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    public function post($path, $controllerAction, $middlewares = [])
    {
        $this->routes['POST'][$path] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    public function put($path, $controllerAction, $middlewares = [])
    {
        $this->routes['PUT'][$path] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    public function delete($path, $controllerAction, $middlewares = [])
    {
        $this->routes['DELETE'][$path] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    private function compilePattern(string $pattern): ?string
    {
        if (strpos($pattern, '{') === false) {
            return null;
        }
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^\/]+)', trim($pattern, '/'));
        return '#^' . $regex . '$#';
    }

    public function routeParams(): array
    {
        return $this->route_params;
    }

    public function dispatch(){
        $method = $_SERVER['REQUEST_METHOD'];
        $path_found = false;
        $route_config = null;
        $this->route_params = [];

        foreach ($this->routes as $route_method => $paths){
            if(array_key_exists($this->current_path, $paths)){
                $path_found = true;
                break;
            }
        }

        if (!$path_found && isset($this->routes[$method])) {
            $path_to_match = trim($this->current_path, '/');
            foreach ($this->routes[$method] as $pattern => $config) {
                $regex = $this->compilePattern($pattern);
                if ($regex !== null && preg_match($regex, $path_to_match, $matches)) {
                    foreach ($matches as $key => $value) {
                        if (!is_int($key)) {
                            $this->route_params[$key] = urldecode($value);
                        }
                    }
                    $route_config = $config;
                    $path_found = true;
                    break;
                }
            }
        }

        if($path_found){
            if ($route_config === null && isset($this->routes[$method][$this->current_path])) {
                $route_config = $this->routes[$method][$this->current_path];
            }

            if ($route_config !== null) {
                $action = $route_config['action'];
                $middlewares = $route_config['middlewares'];

                foreach ($middlewares as $middleware) {
                    $middlewareInstance = new $middleware();
                    
                    if (!$middlewareInstance->handle()) {
                        return; 
                    }
                }
                $result = $action($this->route_params);
                if ($result !== null) {
                    echo json_encode($result);
                }
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Route not found!"]);
            }
        }else {
            http_response_code(404);
            echo json_encode(["error" => "Route not found!"]);
        }
    }
}