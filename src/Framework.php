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
use Opine\Person;
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
    private $apiToken = false;
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
        $this->apiToken = Person::apiTokenFromRequest();
        $items = [
            $this->root . '-collections' => false,
            $this->root . '-forms' => false,
            $this->root . '-bundles' => false,
            $this->root . '-topics' => false,
            $this->root . '-routes' => false,
            $this->root . '-container' => false,
            $this->root . '-languages' => false,
            $this->root . '-config' => false,
            'xxx' => false
        ];
        if ($this->apiToken !== false) {
            $items['person-' . $this->apiToken] = false;
        }
        $cache = new Cache();
        $cacheResult = $cache->getBatch($items);
        if ($noContainerCache === false && $cacheResult === true) {
            $noContainerCache = json_decode($items[$this->root . '-container'], true);
        }
        if ($items[$this->root . '-routes'] != false) {
            $this->routeCached = true;
        }
        self::$container = new Container($this->root, $this->root . '/../container.yml', $noContainerCache);
        $this->cache($items);
    }

    public function root () {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
        if (substr($root, -6, 6) != 'public' && file_exists($root . '/public')) {
            $root .= '/public';
        }
        return $root;
    }

    public function routing () {
        self::$container->imageResizerRoute->paths();
        self::$container->collectionRoute->paths();
        self::$container->formRoute->paths();
        self::$container->bundleModel->paths();
        $myRoute = new Route(self::$container->route);
        if (method_exists($myRoute, 'paths')) {
            $myRoute->paths();
        }
    }

    public function frontController () {
        if (isset($_POST) && !empty($_POST)) {
            $uriBase = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            self::$container->post->populate($uriBase, $_POST);
        }
        if ($this->routeCached === false) {
            $this->routing();
        }
        http_response_code(200);
        try {
            $path = self::$container->language->pathEvaluate($this->pathDetermine());
            $response = self::$container->route->run($_SERVER['REQUEST_METHOD'], $path);
            echo $response;
        } catch (Exception $e) {
            if (http_response_code() == 200) {
                http_response_code(500);
            }
            echo $e->getMessage();
        }
    }

    private function pathDetermine () {
        $path = $_SERVER['REQUEST_URI'];
        if (substr_count($path, '?') > 0) {
            $path = str_replace('?' . $_SERVER['QUERY_STRING'], '', $path);
        }
        return $path;
    }

    public function cache (array &$items) {
        self::$container->collectionModel->cacheSet(json_decode($items[$this->root . '-collections'], true));
        self::$container->formModel->cacheSet(json_decode($items[$this->root . '-forms'], true));
        self::$container->bundleModel->cacheSet(json_decode($items[$this->root . '-bundles'], true));
        self::$container->topic->cacheSet(json_decode($items[$this->root . '-topics'], true));
        self::$container->route->cacheSet(json_decode($items[$this->root . '-routes'], true));
        self::$container->language->cacheSet(json_decode($items[$this->root . '-languages'], true));
        $environment = 'default';
        if (isset($_SERVER['OPINE-ENV'])) {
            $environment = $_SERVER['OPINE-ENV'];
        }
        $config = json_decode($items[$this->root . '-config'], true);
        if (isset($config[$environment])) {
            self::$container->config->cacheSet($config[$environment]);
        } elseif (isset($config['default'])) {
            self::$container->config->cacheSet($config['default']);
        } else {
            self::$container->config->cacheSet(false);
        }
        if ($this->apiToken !== false) {
            if ($items['person-' . $this->apiToken] != false) {
                self::$container->person->establish(json_decode($items['person-' . $this->apiToken], true));
            } else {
                $person = self::$container->person->findByApiToken($this->apiToken, true);
                if ($person != false) {
                    self::$container->person->establish($person);
                }
            }
        }
    }
}