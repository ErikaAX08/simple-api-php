<?php

header("Access-Control-Allow-Origin: *");
header(
    "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"
);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("content-type: application/json; charset=utf-8");

require_once __DIR__ . "/vendor/autoload.php";

use SimpleAPI\Router\Router;

$router = new Router();
$router->run();

?>
