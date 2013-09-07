<?php
require __DIR__ . '/lib/separation/Separation.php';
require __DIR__ . '/lib/Collection.php';
require __DIR__ . '/lib/FrontController.php';
require __DIR__ . '/Slim/Slim.php';
require __DIR__ . '/lib/Event.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$routePath = __DIR__ . '/site/Route.php';
if (!file_exists($routePath)) {
    exit('Route.php file undefined in site.');
}
require $routePath;
if (!class_exists('Route')) {
    exit ('Route class not defined properly.');
}
Separation::config([
	'layouts' 		=> __DIR__ . '/example/layouts/',
	'templates' 	=> __DIR__ . '/example/templates/',
	'sep'			=> __DIR__ . '/example/sep/'
]);
$route = new Route();
FrontController::routeCollections($app, $route);
FrontController::routeCustom($app, $route);
$app->run();