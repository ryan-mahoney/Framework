<?php
class Framework {
	public static function routeCustom (&$app, &$route) {
		if (!method_exists($route, 'custom')) {
			return;
		}
		$route->custom($app);
	}

	public static function route () {
		if (php_sapi_name() == 'cli') {
			if ($_SERVER['argv'][1] != 'build') {
				exit;
			}
			Build::project($_SERVER['PWD']);
		}
		\Slim\Slim::registerAutoloader();
		$app = new \Slim\Slim();
		$routePath = $_SERVER['DOCUMENT_ROOT'] . '/Route.php';
		if (!file_exists($routePath)) {
    		exit('Route.php file undefined in site.');
		}
		require $routePath;
		if (!class_exists('Route')) {
    		exit ('Route class not defined properly.');
		}
		Separation::config([
			'partials' 	=> $_SERVER['DOCUMENT_ROOT'] . '/partials/',
			'layouts' 		=> $_SERVER['DOCUMENT_ROOT'] . '/layouts/',
			'sep'			=> $_SERVER['DOCUMENT_ROOT'] . '/sep/'
		]);
		CollectionRoute::json($app);
		CollectionRout::pages($app);
		$route = new Route();
		self::routeCustom($app, $route);
		$app->run();
	}
}