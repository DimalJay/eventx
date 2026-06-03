<?php

namespace Routes;

class Router
{
    private $current_path;
    private $routes = [];
    public function __construct($baseUrl = "api/v1")
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        $request_url = isset($_GET['request']) ? trim($_GET['request'], '/') : '';

        $url_segments = explode($baseUrl, $request_url);
        $raw_path = isset($url_segments[1]) ? $url_segments[1] : '';
        $this->current_path = '/' . trim($raw_path, '/');
    }

    public function get($path, $controllerAction)
    {
        $this->routes['GET'][$path] = $controllerAction;
    }

    public function post($path, $controllerAction)
    {
        $this->routes['POST'][$path] = $controllerAction;
    }

    public function put($path, $controllerAction)
    {
        $this->routes['PUT'][$path] = $controllerAction;
    }

    public function delete($path, $controllerAction)
    {
        $this->routes['DELETE'][$path] = $controllerAction;
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
                $action = $this->routes[$method][$this->current_path];
                $result = $action();
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Route not found!"]);
            }
        }
    }
}