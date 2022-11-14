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
            ResponseController::LogError("Not found");
        } else {
            $controller = $_SERVER["REQUEST_METHOD"] . "Controller";
            $controller = "SimpleAPI\Controllers\\$controller";

            if (class_exists($controller)) {
                $controller = new $controller($routes[1]);
                call_user_func([$controller, "response"]);
            } else {
                echo ResponseController::LogError(
                    "HTTPS method does not exist"
                );
            }
        }
    }
}

?>
