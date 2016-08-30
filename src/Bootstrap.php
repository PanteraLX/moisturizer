<?php

namespace Moisturizer;

use FastRoute\RouteCollector;
use Http\HttpRequest;
use Http\HttpResponse;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require '../vendor/autoload.php';

error_reporting(E_ALL);

$environment = 'development';

/**
 * Register the error handler
 */
$whoops = new Run();
if ($environment !== 'production') {
    $whoops->pushHandler(new PrettyPageHandler());
} else {
    $whoops->pushHandler(function($e){
        echo 'Friendly error page and send an email to the developer';
    });
}
$whoops->register();

$request = new HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
$response = new HttpResponse();

$routeDefinitionCallback = function (\FastRoute\RouteCollector $r) {
    $routes = include('Routes.php');
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
};
$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPath());
switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        $response->setContent('404 - Page not found');
        $response->setStatusCode(404);
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response->setContent('405 - Method not allowed');
        $response->setStatusCode(405);
        break;
    case \FastRoute\Dispatcher::FOUND:
        $className = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];

        $class = new $className;
        $class->$method($vars);
        break;
}
foreach ($response->getHeaders() as $header) {
    header($header, false);
}
echo $response->getContent();

echo $response->getContent();
