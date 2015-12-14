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

class Exception extends BaseException
{
}

class Framework
{
    private $container;
    private $routeCached = false;
    private $apiToken;
    private $root;
    private $environment;
    private $cachePrefix;

    private function environment()
    {
        // set environment
        $this->environment = 'default';
        $test = getenv('OPINE_ENV');
        if (!empty($test)) {
            $this->environment = $test;
        }

        // set project
        $projectName = 'project';
        $test = getenv('OPINE_PROJECT');
        if ($test !== false) {
            $projectName = $test;
        }

        $this->cachePrefix = $projectName . $this->environment;
    }

    private function errors()
    {
        if ($this->environment == 'production') {
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

    public function __construct($noContainerCache = false)
    {
        $cache = new Cache($this->root);
        $this->environment();
        $this->errors();
        $this->root = $this->root();
        $this->apiToken = $this->apiTokenFromRequest();
        $items = ['collections', 'forms', 'bundles', 'topics', 'routes', 'container', 'languages', 'config'];
        $person = false;
        if (!empty($this->apiToken)) {
            $person = json_decode($cache->get('person-'.$this->apiToken), true);
        }
        $cacheResult = json_decode($cache->get($this->cachePrefix . '-opine'), true);
        $containerCache = [];
        if ($noContainerCache === false && isset($cacheResult['container'])) {
            $containerCache = $cacheResult['container'];
        }
        if ($cacheResult['routes'] != false) {
            $this->routeCached = true;
        }
        $config = new Config($this->root);
        if ($cacheResult['config'] !== false) {
            $configData = json_decode($cacheResult['config'], true);
            if (isset($configData[$this->environment])) {
                $config->cacheSet($configData[$this->environment]);
            } elseif (isset($configData['default'])) {
                $config->cacheSet($configData['default']);
            }
        } else {
            $config->cacheSet();
        }
        $this->container = Container::instance($this->root, $config, $this->root.'/../config/containers/container.yml', $noContainerCache, $containerCache);
        $this->container->set('cache', $cache);
        $this->cache($cacheResult, $person);
    }

    public function root()
    {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getcwd() : $_SERVER['DOCUMENT_ROOT']);
        if (substr($root, -6, 6) != 'public' && file_exists($root.'/public')) {
            $root .= '/public';
        }

        return $root;
    }

    public function routing()
    {
        $this->container->get('imageResizerRoute')->paths();
        $this->container->get('collectionRoute')->paths();
        $this->container->get('formRoute')->paths();
    }

    public function frontController()
    {
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

    private function pathDetermine()
    {
        $path = $_SERVER['REQUEST_URI'];
        if (substr_count($path, '?') > 0) {
            $path = str_replace('?'.$_SERVER['QUERY_STRING'], '', $path);
        }

        return $path;
    }

    public function cache(array &$cacheResult, $person)
    {
        $this->container->get('collectionModel')->cacheSet($cacheResult['collections']);
        $this->container->get('formModel')->cacheSet($cacheResult['forms']);
        $this->container->get('bundleModel')->cacheSet($cacheResult['bundles']);
        $this->container->get('topic')->cacheSet($cacheResult['topics']);
        $this->container->get('route')->cacheSet($cacheResult['routes']);
        $this->container->get('language')->cacheSet($cacheResult['languages']);
        if (!empty($person)) {
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

    private function apiTokenFromRequest()
    {
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
