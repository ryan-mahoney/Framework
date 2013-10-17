<?php
namespace Framework;
use Container\Container;

class Framework {
	public function frontController () {
		$sapi = php_sapi_name();
		$root = (($sapi == 'cli') ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
		$container = new Container($root);
		if ($sapi == 'cli') {
			if (!isset($_SERVER['argv'][1]) || $_SERVER['argv'][1] != 'build') {
				exit;
			}
			$container->build->project($root);
		}
		$slim = $container->slim;
		if (isset($_POST) && !empty($_POST)) {
			$container->post->populate($slim->request->getResourceUri(), $_POST);
		}
		
		//configuration cache
		$items = [$root . '-collections.json' => false, $root . '-filters.json' => false, $root . '-helpers.json' => false];
		$result = $container->cache->getBatch($items);
		if ($result === true) {
			$container->collectionRoute->cacheSet(json_decode($items[$root . '-collections.json'], true));
			$container->filter->cacheSet(json_decode($items[$root . '-filters.json'], true));
			$container->helperRoute->cacheSet(json_decode($items[$root . '-helpers.json'], true));
			//form cache
		}

		//smart routing
		$container->helperRoute->helpers($root);
		$container->collectionRoute->json($root);
		$container->collectionRoute->app($root);
		$container->collectionRoute->collectionList($root);
		$container->formRoute->json($root);
		$container->formRoute->app($root);
		$container->manager->app($root);
		$container->imageResizer->route();
		
		//custom routing
		$routePath = $root . '/Route.php';
		if (!file_exists($routePath)) {
    		exit('Route.php file undefined for project.');
		}
		require $routePath;
		if (!class_exists('\Route')) {
    		exit ('Route class not defined properly.');
		}
		$myRoute = new \Route($container);
		if (method_exists($myRoute, 'custom')) {
			$myRoute->custom();
		}

		//generate output
		$this->routeList($slim);
		$slim->run();
		$container->filter->apply($root);
		echo $container->response;
	}

	private function routeList ($slim) {
		$slim->get('/routes', function () use ($slim) {
			$routes = $slim->router()->getNamedRoutes();
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