<?php
namespace Framework;
use Container\Container;

class Framework {
	public function frontController () {
		$sapi = php_sapi_name();
		$root = (($sapi == 'cli') ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
		$container = new Container($root . '/container.yml');
		if ($sapi == 'cli') {
			if (!isset($_SERVER['argv'][1]) || $_SERVER['argv'][1] != 'build') {
				exit;
			}
			$container->build->project($root);
		}
		$cache = $container->cache;
		$collectionRoute = $container->collectionRoute;
		$filter = $container->filter;
		$helperRoute = $container->helperRoute;
		$eventRoute = $container->eventRoute;
		$formRoute = $container->formRoute;
		$slim = $container->slim;
		$imageResizer = $container->imageResizer;
		$separation = $container->separation;
		
		//configuration cache
		$items = [
			$root . '-collections.json' => false,
			$root . '-filters.json' => false,
			$root . '-helpers.json' => false,
			$root . '-events.json' => false
		];
		$result = $cache->getBatch($items);
		if ($result === true) {
			$collectionRoute->cacheSet(json_decode($items[$root . '-collections.json'], true));
			$filter->cacheSet(json_decode($items[$root . '-filters.json'], true));
			$helperRoute->cacheSet(json_decode($items[$root . '-helpers.json'], true));
			$eventRoute->cacheSet(json_decode($items[$root . '-events.json'], true));
		}

		//smart routing
		$slim::registerAutoloader();
		$this->routeList($slim);
		$this->collectionList($slim);
		$routePath = $root . '/Route.php';
		if (!file_exists($routePath)) {
    		exit('Route.php file undefined for site.');
		}
		require $routePath;
		if (!class_exists('\Route')) {
    		exit ('Route class not defined properly.');
		}
		$helperRoute->helpers($root);
		$collectionRoute->json($root);
		$collectionRoute->pages($root);
		$eventRoute->events($root);
		$formRoute->json($root);
		$formRoute->pages($root);
		$imageResizer->route();
		$myRoute = new \Route($container);
		if (method_exists($myRoute, 'custom')) {
			$myRoute->custom();
		}
		
		//generate output
		ob_start();
		$slim->run();
		$return = ob_get_clean();
		$filter->apply($root, $return);
		echo $return;
	}

	private function routeList ($app) {
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

	private function collectionList ($app) {
		$app->get('/collections', function () use ($app) {
			$collections = (array)json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/collections/cache.json'), true);
			echo '<html><body>';
			foreach ($collections as $collection) {
				echo '<a href="/json-data/' . $collection['p'] . '/all?pretty">', $collection['p'], '</a><br />';
			}
			echo '</body></html>';
			exit;
		})->name('collections');
	}
}