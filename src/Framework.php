<?php
/**
 * Opine\Framework
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Opine;
use Opine\Container;

function container () {
    return Framework::container();
}

class Framework {
    private static $container;
    private static $keyCache = [];

    public static function keySet ($name, $value) {
        self::$keyCache[$name] = $value;
    }

    public static function keyGet ($name) {
        if (!isset(self::$keyCache[$name])) {
            return false;
        }
        return self::$keyCache[$name];
    }

    public static function container () {
        return self::$container;
    }

    public function __construct () {
        $root = $this->root();
        self::$container = new Container($root, $root . '/../container.yml');
    }

    public function commandLine () {
        $root = $this->root();
        $container = self::$container;
        if (!isset($_SERVER['argv'][1])) {
            exit;
        }
        $command = $_SERVER['argv'][1];
        switch ($command) {
            case 'build':
                $container->build->project($root);
                exit;
                break;

            case 'worker':
                set_time_limit(0);
                $this->cache($root, $container);
                $container->topic->load($root);
                $container->worker->work();
                exit;
                break;

            case 'upgrade':
                $container->build->upgrade($root);
                exit;
                break;

            case 'check':
                $container->build->environmentCheck($root);
                exit;

            case 'dburi':
                $container->dbmigration->addURI();
                exit;

            case 'reindex':
                exit;

            case 'topics':
                $this->cache($root, $container);
                $container->topic->load($root);
                $container->topic->show();
                exit;

            case 'count':
                $container->collection->statsAll();
                exit;
        }
    }

    private function root () {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
        if (substr($root, -6, 6) != 'public' && file_exists($root . '/public')) {
            $root .= '/public';
        }
        return $root;
    }

    public function frontController () {
        $root = $this->root();
        $container = self::$container;
        if (strlen(session_id()) == 0) {
            session_start();
        }
        if (isset($_POST) && !empty($_POST)) {
            $uriBase = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            $container->post->populate($uriBase, $_POST);
        }
        
        //configuration cache
        $this->cache($root, $container);

        //smart routing
        $container->imageResizer->route();
        $container->helperRoute->helpers($root);
        $container->collectionRoute->json($root);
        $container->collectionRoute->app($root);
        $container->collectionRoute->collectionList($root);
        $container->formRoute->json();
        $container->formRoute->app($root);
        $container->topic->load($root);
        $container->bundleRoute->app($root);
        $container->authentication->aclRoute();
        
        //custom routing
        $routePath = $root . '/../Route.php';
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
        $slim = $container->slim;
        //$this->routeList($slim);
        $slim->run();
        //$container->filter->apply($root);
        echo $container->response;
    }

    public function react ($port) {
        $root = $this->root();
        $_SERVER['DOCUMENT_ROOT'] = $root;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_NAME'] = 'react';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        ob_start();
        $this->frontController();
        ob_end_clean();

        $container = self::$container;

        $app = function ($request, $response) use (&$container) {
            $text = '';
            $container->response->body = '';
            $slimResponse = false;
            ob_start();
            
            try {
                $querystring = '';
                if (is_array($request->getQuery()) && !empty($request->getQuery())) {
                    $querystring = http_build_query($request->getQuery());
                }
                $url = $request->getPath();
                if ($querystring != '') {
                    $url .= '?' . $querystring;
                }
                $_SERVER['REQUEST_URI'] = $url;
                $_SERVER['QUERY_STRING'] = $querystring;
                $container->bundleRoute->app($root);
                $container->authentication->aclRoute();
                $slimResponse = $container->slim->subRequest($url, $request->getMethod());
            } catch (\Exception $e) {
                echo $e->getMessage();
                $slimResponse = false;
            }

            $text = ob_get_clean();
            if ($container->response->body != '') {
                $text = $container->response->body;
            }
            if ($slimResponse !== false) {
                echo $slimResponse->getStatus(), ' : ', $request->getPath(), "\n";
                $response->writeHead($slimResponse->getStatus(), $slimResponse->getHeaders()->all());
            } else {
                $response->writeHead(500, array());
            }
            $response->end($text, true);
        };

        $loop = React\EventLoop\Factory::create();
        $socket = new React\Socket\Server($loop);
        $http = new React\Http\Server($socket);
        $http->on('request', $app);
        $socket->listen($port);
        $loop->run();
    }

    private function cache ($root, $container) {
        $items = [
            $root . '-collections.json' => false,
            $root . '-filters.json' => false,
            $root . '-forms.json' => false,
            $root . '-bundles.json' => false,
            $root . '-topics.json' => false,
            $root . '-acl.json' => false
        ];
        $result = $container->cache->getBatch($items);
        if ($result === true) {
            $container->collectionRoute->cacheSet(json_decode($items[$root . '-collections.json'], true));
            $container->filter->cacheSet(json_decode($items[$root . '-filters.json'], true));
            $container->formRoute->cacheSet(json_decode($items[$root . '-forms.json'], true));
            $container->bundleRoute->cacheSet(json_decode($items[$root . '-bundles.json'], true));
            $container->topic->cacheSet(json_decode($items[$root . '-topics.json'], true));
            $container->authentication->cacheSet(json_decode($items[$root . '-acl.json'], true));
        }
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