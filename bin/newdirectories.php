#!/usr/bin/env php
<?php
$dir = getcwd();
$mode = 'stage';
if (isset($argv[1]) && $argv[1] == 'run') {
    $mode = 'run';
}

$files = [
    $dir.'/configs'                => $dir.'/config',
    $dir.'/controllers'            => $dir.'/app/controllers',
    $dir.'/models'                 => $dir.'/app/models',
    $dir.'/views'                  => $dir.'/app/views',
    $dir.'/helpers'                => $dir.'/app/helpers',
    $dir.'/Route.php'              => $dir.'/app/Route.php',
    $dir.'/topics.yml'             => $dir.'/config/topics.yml',
    $dir.'/bundles/bundles.yml'    => $dir.'/config/bundles.yml',
    $dir.'/container.yml'          => $dir.'/config/container.yml',
    $dir.'/package-container.yml'  => $dir.'/config/package-container.yml',
    $dir.'/collections'            => $dir.'/config/collections',
    $dir.'/forms'                  => $dir.'/config/forms',
    $dir.'/managers'               => $dir.'/config/managers',
    $dir.'/cache'                  => $dir.'/var/cache',
];

if (!file_exists($dir.'/configs') && !file_exists($dir.'/config')) {
    if ($mode == 'stage') {
        echo 'mkdir '.$dir.'/config', "\n";
    } else {
        mkdir($dir.'/config');
    }
}

if (!file_exists($dir.'/app')) {
    if ($mode == 'stage') {
        echo 'mkdir '.$dir.'/app', "\n";
    } else {
        mkdir($dir.'/app');
    }
}

if (!file_exists($dir.'/var')) {
    if ($mode == 'stage') {
        echo 'mkdir '.$dir.'/var', "\n";
    } else {
        mkdir($dir.'/var');
    }
}

foreach ($files as $file => $newFile) {
    $shortName = str_replace($dir, '', $file);
    if (!file_exists($file)) {
        echo $shortName, ': does not exist.', "\n";
        continue;
    }
    $cmd = 'mv '.$file.' '.$newFile;
    if ($mode == 'stage') {
        echo $cmd, "\n";
    } else {
        shell_exec($cmd);
    }
}

if (file_exists($dir.'/composer.json')) {
    $replaces = [
        '"controllers"' => '"app/controllers"',
        '"helpers"'     => '"app/helpers"',
        '"models"'      => '"app/models"',
        '"views"'       => '"app/views"',
        '"Route.php"'   => '"app/Route.php"',
        '"collections"' => '"config/collections"',
        '"forms"'       => '"config/forms"',
        '"managers"'    => '"config/managers"',
    ];
    $composer = file_get_contents($dir.'/composer.json');
    foreach ($replaces as $replace => $newReplace) {
        $composer = str_replace($replace, $newReplace, $composer);
    }
    if ($mode == 'stage') {
        echo "\n", $composer;
    } else {
        file_put_contents($dir.'/composer.json', $composer);
    }
}
