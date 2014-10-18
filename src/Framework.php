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
use Opine\Cache;
use Route;
use Exception;

function container ($nocache=false) {
    if (Framework::container() == null) {
        new Framework($nocache);
    }
    return Framework::container();
}

class Framework {
    private static $container;
    private static $keyCache = [];
    private $routeCached = false;
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

    public function __construct ($noContainerCache=false) {
        $this->root = $this->root();
        $items = [
            $this->root . '-collections' => false,
            $this->root . '-forms' => false,
            $this->root . '-bundles' => false,
            $this->root . '-topics' => false,
            $this->root . '-routes' => false,
            $this->root . '-container' => false
        ];
        $cache = new Cache();
        $cacheResult = $cache->getBatch($items);
        if ($noContainerCache === false && $cacheResult === true) {
            $noContainerCache = json_decode($items[$this->root . '-container'], true);
        }
        if ($items[$this->root . '-routes'] != false) {
            $this->routeCached = true;
        }
        self::$container = new Container($this->root, $this->root . '/../container.yml', $noContainerCache);
        if ($cacheResult === true) {
            $this->cache($items);
        }
    }

    public function root () {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
        if (substr($root, -6, 6) != 'public' && file_exists($root . '/public')) {
            $root .= '/public';
        }
        return $root;
    }

    public function routing () {
        self::$container->imageResizer->paths();
        self::$container->collectionRoute->paths();
        self::$container->formRoute->paths();
        self::$container->bundleRoute->paths();
        $myRoute = new Route(self::$container->route);
        if (method_exists($myRoute, 'paths')) {
            $myRoute->paths();
        }
    }

    public function frontController () {
        if (strlen(session_id()) == 0) {
            session_start();
        }
        if (isset($_POST) && !empty($_POST)) {
            $uriBase = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            self::$container->post->populate($uriBase, $_POST);
        }
        if ($this->routeCached === false) {
            $this->routing();
        }
        http_response_code(200);
        try {
            $response = self::$container->route->run();
            echo $response;
        } catch (Exception $e) {
            if (http_response_code() == 200) {
                http_response_code(500);
            }
            echo $e->getMessage();
        }
    }

    public function cache (array &$items) {        
        self::$container->collectionModel->cacheSet(json_decode($items[$this->root . '-collections'], true));
        self::$container->formModel->cacheSet(json_decode($items[$this->root . '-forms'], true));
        self::$container->bundleRoute->cacheSet(json_decode($items[$this->root . '-bundles'], true));
        self::$container->topic->cacheSet(json_decode($items[$this->root . '-topics'], true));
        self::$container->route->cacheSet(json_decode($items[$this->root . '-routes'], true));
    }
}