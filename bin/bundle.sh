#!/usr/bin/env php
<?php
$directory = getcwd();
if (!is_writable($directory)) {
    echo 'You need write permissions for this directory.', "\n";
    exit;
}
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    echo 'You need at least PHP version 5.4.0, your version us: ', PHP_VERSION, "\n";
    exit;
}

date_default_timezone_set('UTC');

$directory = getcwd();
$pieces = explode('/', $directory);
$serviceName = array_pop($pieces);
$bundleRoot = $directory . '/src/' . $serviceName;

$directories = [
    $directory . '/src',
    $bundleRoot,
    $bundleRoot . '/public',
    $bundleRoot . '/public/css',
    $bundleRoot . '/public/fonts',
    $bundleRoot . '/public/helpers',
    $bundleRoot . '/public/images',
    $bundleRoot . '/public/js',
    $bundleRoot . '/public/layouts',
    $bundleRoot . '/public/partials',
    $bundleRoot . '/app',
    $bundleRoot . '/app/models',
    $bundleRoot . '/app/collections',
    $bundleRoot . '/app/documents',
    $bundleRoot . '/app/forms',
    $bundleRoot . '/fields',
    $bundleRoot . '/forms',
    $bundleRoot . '/collections',
    $bundleRoot . '/controllers',
    $bundleRoot . '/models',
    $bundleRoot . '/views',
    $bundleRoot . '/managers'
];

foreach ($directories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory);
    }
}

$bundleRouteRoot = $bundleRoot . '/Route.php';
if (!file_exists($bundleRouteRoot)) {
    file_put_contents($bundleRouteRoot, '<?php
namespace Opine\\' . $serviceName . ';

class Route {
    private $route;

    public function __construct ($route) {
        $this->route = $route;
    }

    public function paths () {}

    public static function location () {
        return __DIR__;
    }
}');