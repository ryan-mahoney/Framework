<?php
namespace Framework;
use Slim\Slim;
use Build\Build;
use Separation\Separation;
use Collection\CollectionRoute;
use Form\FormRoute;
use Event\EventRoute;
use Helper\HelperRoute;
use Filter\Filter;
use Cache\Cache;

class Framework {
	public static function route () {
		if (php_sapi_name() == 'cli') {
			if ($_SERVER['argv'][1] != 'build') {
				exit;
			}
			Build::project(getcwd ());
		}
		self::configCache($_SERVER['DOCUMENT_ROOT']);
		Slim::registerAutoloader();
		$app = new Slim();
		self::routeList($app);
		self::collectionList($app);
		//self::separationBuilder($app);
		$routePath = $_SERVER['DOCUMENT_ROOT'] . '/Route.php';
		if (!file_exists($routePath)) {
    		exit('Route.php file undefined for site.');
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
		HelperRoute::helpers();
		CollectionRoute::json($app);
		CollectionRoute::pages($app);
		EventRoute::events();
		FormRoute::json($app);
		FormRoute::pages($app);
		$route = new \Route();
		self::routeCustom($app, $route);
		ob_start();
		$app->run();
		$return = ob_get_clean();
		Filter::apply($return);
		echo $return;
	}

	private static function configCache ($root) {
		$items = [
			$root . '-collections.json' => false,
			$root . '-filters.json' => false,
			$root . '-helpers.json' => false,
			$root . '-events.json' => false
		];
		$result = Cache::getBatch($items);
		if ($result === true) {
			CollectionRoute::cacheSet(json_decode($items[$root . '-collections.json'], true));
			Filter::cacheSet(json_decode($items[$root . '-filters.json'], true));
			HelperRoute::cacheSet(json_decode($items[$root . '-helpers.json'], true));
			EventRoute::cacheSet(json_decode($items[$root . '-events.json'], true));
		}
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

	private static function collectionList ($app) {
		$app->get('/collections', function () use ($app) {
			$collections = (array)json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/collections/cache.json'), true);
			print_r($collections);
			exit;
			echo '<html><body>';
			foreach ($collections as $colection) {
				echo '<a href="http://json.virtuecenter.com/json-data/' . $colection['p'] . '/all?pretty" target="_new">', $collection['p'], '</a><br />';
			}
			echo '</body></html>';
			exit;
		})->name('collections');
	}
}
