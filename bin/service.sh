#!/usr/bin/env php
<?php
$namespace = 'Opine';
if (isset($argv[1]) && !empty($argv[1])) {
    $namespace = $argv[1];
}
$directory = getcwd();
if (!is_writable($directory)) {
    echo 'You need write permissions for this directory.', "\n";
    exit;
}
if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    echo 'You need at least PHP version 5.5.0, your version us: ', PHP_VERSION, "\n";
    exit;
}
date_default_timezone_set('UTC');
$pieces = explode('/', $directory);
$serviceName = array_pop($pieces);

@mkdir($directory . '/src');
put($directory . '/src/' . $serviceName . '.php', '<?php
/**
 * ' . $namespace . '\\' . $serviceName . '
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
namespace ' . $namespace . ';

class ' . $serviceName . ' {
}');

@mkdir ($directory . '/config');
put($directory . '/config/db.php', '<?php
return [
  \'name\' => \'phpunit\',
  \'conn\' => \'mongodb://unit:test@localhost/phpunit\'
];');

@mkdir ($directory . '/tests');
put($directory . '/tests/bootstrap.php', '<?php
date_default_timezone_set(\'UTC\');
require_once __DIR__ . \'/../vendor/autoload.php\';
');

@mkdir ($directory . '/public');
put($directory . '/public/placeholder', '');

put($directory . '/tests/' . $serviceName . 'Test.php', '<?php
namespace ' . $namespace . ';
use PHPUnit_Framework_TestCase;

class ' . $serviceName . 'Test extends PHPUnit_Framework_TestCase {
    public function setup () {
        $root = __DIR__ . \'/../public\';
        $container = new Container($root, $root . \'/../container.yml\');
    }

    public function testSample () {
        $this->assertTrue(true);
    }
}');

put($directory . '/composer.json', '{
    "name": "' . strtolower($namespace) . '/' . strtolower($serviceName) . '",
    "type": "library",
    "minimum-stability": "dev",
    "description": "",
    "keywords": [],
    "license": "MIT",
    "authors": [
        {
            "name": "Ryan Mahoney",
            "email": "ryan@virtuecenter.com",
            "homepage": "http://opine-php.org",
            "role": "Developer"
        }
    ],
    "require": {
        "php": ">=5.4.0",
        "phpunit/phpunit": "3.7.32"
    },
    "autoload": {
        "psr-4": {"' . $namespace . '\\\\": "src/"}
    }
}');

put($directory . '/.gitignore', '/vendor
/composer.lock
/report');

put($directory . '/.scrutinizer.yml', 'tools:
    external_code_coverage: true
');

put($directory . '/.travis.yml', 'language: php
php:
  - 5.5

install:
  - composer self-update
  - composer install

services:
  - memcached
  - mongodb

before_script:
  - echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  - echo "extension = memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  - mongo phpunit --eval \'db.addUser("unit", "test");\'

script: vendor/bin/phpunit --coverage-clover=coverage.clover

after_script:
  - wget https://scrutinizer-ci.com/ocular.phar
  - php ocular.phar code-coverage:upload --format=php-clover coverage.clover
');

put($directory . '/container.yml', "services:");

put($directory . '/phpunit.xml', '<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="./tests/bootstrap.php" colors="true">
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./tests/</directory>
        </testsuite>
    </testsuites>
</phpunit>');

put($directory . '/README.md', $serviceName);

function put ($file, $data) {
    if (file_exists($file)) {
        return;
    }
    file_put_contents($file, $data);
}