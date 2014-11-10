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
    $bundleRoot . '/app/collections',
    $bundleRoot . '/app/documents',
    $bundleRoot . '/app/forms',
    $bundleRoot . '/fields',
    $bundleRoot . '/forms',
    $bundleRoot . '/collections'
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
    private $root;
    private $bundleModel;
    private $route;

    public function __construct ($container, $root, $bundleModel) {
        $this->root = $root;
        $this->bundleModel = $bundleModel;
        $this->route = $container->route;
    }

    public function paths () {}
}');
}