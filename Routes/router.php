<?php

namespace Routes;

class Router
{
    private $current_path;
    private $routes = [];
    public function __construct($baseUrl = "api/v1")
    {
        $allowedOrigins = [
            "http://localhost:3000"
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

        $request_url = isset($_GET['request']) ? trim($_GET['request'], '/') : '';

        $url_segments = explode($baseUrl, $request_url);
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

    public function dispatch(){
        $method = $_SERVER['REQUEST_METHOD'];
        $path_found = false;

        foreach ($this->routes as $route_method => $paths){
            if(array_key_exists($this->current_path, $paths)){
                $path_found = true;
                break;
            }
        }

        if($path_found){
            if(isset($this->routes[$method][$this->current_path])){
                $route_config = $this->routes[$method][$this->current_path];
                $action = $route_config['action'];
                $middlewares = $route_config['middlewares'];

                foreach ($middlewares as $middleware) {
                    $middlewareInstance = new $middleware();
                    
                    if (!$middlewareInstance->handle()) {
                        return; 
                    }
                }
                $result = $action();
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