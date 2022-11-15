<?php

namespace SimpleAPI\Router;

use SimpleAPI\Controllers\ResponseController;

class Router
{
    public function run()
    {
        $routes = explode("/", $_SERVER["REQUEST_URI"]);
        $routes = array_filter($routes);
        if (count($routes) === 0) {
            echo ResponseController::LogError(400, "Malformed request syntax");
        } else {
            $controller = $_SERVER["REQUEST_METHOD"] . "Controller";
            $controller = "SimpleAPI\Controllers\\$controller";

            if (class_exists($controller)) {
                $controller = new $controller($routes[1]);
                call_user_func([$controller, "response"]);
            } else {
                echo ResponseController::LogError(
                    501,
                    "The request method is not supported by the server and cannot be handled"
                );
            }
        }
    }
}
