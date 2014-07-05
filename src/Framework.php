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
    if (Framework::container() == null) {
        new Framework();
    }
    return Framework::container();
}

class Framework {
    private static $container;
    private static $keyCache = [];
    private static $frontCalled = false;
    private static $responseCode = 200;
    private $root;

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
        $this->root = $this->root();
        self::$container = new Container($this->root, $this->root . '/../container.yml');
    }

    public function root () {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
        if (substr($root, -6, 6) != 'public' && file_exists($root . '/public')) {
            $root .= '/public';
        }
        return $root;
    }

    private function firstCall () {
        if (strlen(session_id()) == 0) {
            session_start();
        }
        if (isset($_POST) && !empty($_POST)) {
            $uriBase = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            $container->post->populate($uriBase, $_POST);
        }
        $this->cache();
        $this->routing();
    }

    public function routing () {
        $container = self::$container;
        $container->imageResizer->paths();
        $container->collectionRoute->paths();
        $container->formRoute->paths();
        $container->bundleRoute->paths();
        $container->helperRoute->helpers();
        $container->authentication->aclRoute();

        $routePath = $this->root . '/../Route.php';
        if (!file_exists($routePath)) {
            exit('Route.php file undefined for project.');
        }
        require $routePath;
        if (!class_exists('\Route')) {
            exit ('Route class not defined properly.');
        }
        $myRoute = new \Route($container->route);
        if (method_exists($myRoute, 'paths')) {
            $myRoute->paths();
        }
    }

    public function frontController () {
        if (self::$frontCalled == false) {
            $this->firstCall();
            self::$frontCalled = true;
        }
        $container = self::$container;
        $route = $container->route;
        http_response_code();
        try {
            $response = $route->run();
            echo $response;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        echo $container->response;
    }

    public function cache () {
        $container = self::$container;
        $items = [
            $this->root . '-collections' => false,
            $this->root . '-forms' => false,
            $this->root . '-bundles' => false,
            $this->root . '-topics' => false,
            $this->root . '-routes' => false
        ];
        $result = $container->cache->getBatch($items);
        if ($result === true) {
            $container->collectionRoute->cacheSet(json_decode($items[$this->root . '-collections'], true));
            $container->formRoute->cacheSet(json_decode($items[$this->root . '-forms'], true));
            $container->bundleRoute->cacheSet(json_decode($items[$this->root . '-bundles'], true));
            $container->topic->cacheSet(json_decode($items[$this->root . '-topics'], true));
            $container->route->cacheSet(json_decode($items[$this->root . '-routes'], true));
            //$container->authentication->cacheSet(json_decode($items[$this->root . '-acl'], true));
        }
    }
}