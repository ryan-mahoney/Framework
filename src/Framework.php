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
		$app->get('/routes', function () use ($app) {
			$routes = $app->router()->getNamedRoutes();
			$paths = [];
			foreach ($routes as $route) {
				$refl = new ReflectionClass($route); 
				$paths[] = [
					'name' => $route->getName(),
					'pattern' => $route->getPattern()
				];
			}
			echo json_encode($paths, JSON_PRETTY_PRINT);
			exit;
		});
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
		CollectionRoute::pages($app);
		FormRoute::json($app);
		FormRoute::pages($app);
		$route = new Route();
		self::routeCustom($app, $route);
		$app->run();
		//apply filters
	}
}