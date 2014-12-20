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
use Opine\Container\Service as Container;
use Opine\Cache\Service as Cache;
use Opine\Config\Service as Config;
use Exception as BaseException;
use Whoops;

class Exception extends BaseException {}

class Framework {
    private $container;
    private $routeCached = false;
    private $apiToken;
    private $root;
    private $environment;

    private function environment () {
        $this->environment = 'default';
        if (isset($_SERVER['OPINE_ENV'])) {
            $this->environment = $_SERVER['OPINE_ENV'];
        }
        if ($this->environment == 'default') {
            $test = getenv('OPINE_ENV');
            if (empty($test)) {
                return;
            }
            $this->environment = $test;
        }
    }

    private function errors () {
        if ($this->environment == 'prod') {
            return;
        }
        $run = new Whoops\Run();
        $handler = new Whoops\Handler\PrettyPageHandler();
        $run->pushHandler($handler);
        $run->pushHandler(function ($exception, $inspector, $run) {
            $inspector->getFrames()->map(function ($frame) {
                return $frame;
            });
        });
        $run->register();
    }

    public function __construct ($noContainerCache=false) {
        $this->environment();
        $this->errors();
        $this->root = $this->root();
        $this->apiToken = $this->apiTokenFromRequest();
        $items = [
            $this->root . '-collections' => false,
            $this->root . '-forms' => false,
            $this->root . '-bundles' => false,
            $this->root . '-topics' => false,
            $this->root . '-routes' => false,
            $this->root . '-container' => false,
            $this->root . '-languages' => false,
            $this->root . '-config' => false
        ];
        if (!empty($this->apiToken)) {
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
        $config = new Config($this->root);
        if ($items[$this->root . '-config'] !== false) {
            $configData = json_decode($items[$this->root . '-config'], true);
            if (isset($configData[$this->environment])) {
                $config->cacheSet($configData[$this->environment]);
            } elseif (isset($configData['default'])) {
                $config->cacheSet($configData['default']);
            }
        } else {
            $config->cacheSet();
        }
        $this->container = Container::instance($this->root, $config, $this->root . '/../config/container.yml', $noContainerCache);
        $this->container->set('cache', $cache);
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
        $this->container->get('imageResizerRoute')->paths();
        $this->container->get('collectionRoute')->paths();
        $this->container->get('formRoute')->paths();
        $this->container->get('bundleModel')->paths();
        $myRoute = new \Route($this->container->get('route'));
        if (method_exists($myRoute, 'paths')) {
            $myRoute->paths();
        }
    }

    public function frontController () {
        if (isset($_POST) && !empty($_POST)) {
            $this->container->get('post')->populate($_POST);
        }
        if ($this->routeCached === false) {
            $this->routing();
        }
        http_response_code(200);
        try {
            $path = $this->container->get('language')->pathEvaluate($this->pathDetermine());
            $response = $this->container->get('route')->run($_SERVER['REQUEST_METHOD'], $path);
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
        $this->container->get('collectionModel')->cacheSet(json_decode($items[$this->root . '-collections'], true));
        $this->container->get('formModel')->cacheSet(json_decode($items[$this->root . '-forms'], true));
        $this->container->get('bundleModel')->cacheSet(json_decode($items[$this->root . '-bundles'], true));
        $this->container->get('topic')->cacheSet(json_decode($items[$this->root . '-topics'], true));
        $this->container->get('route')->cacheSet(json_decode($items[$this->root . '-routes'], true));
        $this->container->get('language')->cacheSet(json_decode($items[$this->root . '-languages'], true));
        if (!empty($this->apiToken)) {
            if ($items['person-' . $this->apiToken] != false) {
                $person = json_decode($items['person-' . $this->apiToken], true);
                $this->container->get('person')->establish($person);
                $this->container->get('db')->userIdSet($person['_id']);
            } else {
                $person = $this->container->get('person')->findByApiToken($this->apiToken, true);
                if ($person != false) {
                    $this->container->get('person')->establish($person);
                    $this->container->get('db')->userIdSet($person['_id']);
                }
            }
        }
    }

    private function apiTokenFromRequest () {
        if (isset($_SERVER['api_token'])) {
            return $_SERVER['api_token'];
        }
        if (isset($_GET['api_token'])) {
            return $_GET['api_token'];
        }
        if (isset($_POST['api_token'])) {
            return $_POST['api_token'];
        }
        if (isset($_COOKIE['api_token'])) {
            return $_COOKIE['api_token'];
        }
        return false;
    }
}