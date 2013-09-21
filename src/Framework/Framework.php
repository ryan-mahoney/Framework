<?php
namespace Framework;
use Slim\Slim;
use Build\Build;
use Separation\Separation;
use Collection\CollectionRoute;
use Form\FormRoute;
use Event\EventRoute;

class Framework {
	public static function route () {
		if (php_sapi_name() == 'cli') {
			if ($_SERVER['argv'][1] != 'build') {
				exit;
			}
			Build::project($_SERVER['PWD']);
		}
		Slim::registerAutoloader();
		$app = new \Slim\Slim();
		self::routeList($app);
		self::separationBuilder($app);
		$routePath = $_SERVER['DOCUMENT_ROOT'] . '/Route.php';
		if (!file_exists($routePath)) {
    		exit('Route.php file undefined in site.');
		}
		require $routePath;
		if (!class_exists('\Route')) {
    		exit ('Route class not defined properly.');
		}
		Separation::config([
			'partials' 		=> $_SERVER['DOCUMENT_ROOT'] . '/partials/',
			'layouts' 		=> $_SERVER['DOCUMENT_ROOT'] . '/layouts/',
			'sep'			=> $_SERVER['DOCUMENT_ROOT'] . '/sep/'
		]);
		CollectionRoute::json($app);
		CollectionRoute::pages($app);
		EventRoute::events();
		FormRoute::json($app);
		FormRoute::pages($app);
		$route = new \Route();
		self::routeCustom($app, $route);
		$app->run();
		//apply filters
	}

	public static function routeCustom (&$app, &$route) {
		if (!method_exists($route, 'custom')) {
			return;
		}
		$route->custom($app);
	}

	private static function separationBuilder ($app) {
		$app->get('/separations', function () {
			$separation = Separation::layout('separation-builder')->template()->write();
		})->name('separation-builder');
		$app->post('/separations', function () {
			print_r($_POST);	
		});
	}

	private static function routeList ($app) {
		$app->get('/routes', function () use ($app) {
			$routes = $app->router()->getNamedRoutes();
			$paths = [];
			echo '<html><body>';
			foreach ($routes as $route) {
				$pattern = $route->getPattern();
				if (substr_count($pattern, '(')) {
					$pattern = explode('(', $pattern, 2)[0];
				}
				echo '<a href="', $pattern, '">', $route->getName(), '</a><br />';
			}
			echo '</body></html>';
			exit;
		})->name('routes');
	}
}