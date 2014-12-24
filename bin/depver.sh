#!/usr/bin/env php
<?php
//usage .framework/bin/depver.sh "opine/bundle" "1.9.0" test
if (!isset($argv[1])) {
    die('first argument is component' . "\n");
}
if (!isset($argv[2])) {
    die('seconf argument is new version' . "\n");
}
$mode = 'test';
if (isset($argv[3])) {
    $mode = $argv[3];
}
$component = $argv[1];
$version = $argv[2];

$cmd = "cd " . __DIR__ . "/../../ && find . | grep composer.json | grep -v bower | grep -v vagrant | sed 's/\.\//" . str_replace('/', '\/', __DIR__ . '/../../') . "/'";
$composers = explode("\n", trim(shell_exec($cmd)));
$pattern = '/("' . str_replace('/', '\/', $component) . '")((?: *)\:(?: *))("[\.0-9\-a-z]*")/';
foreach ($composers as $composer) {
    $content = file_get_contents($composer);
    if (substr_count($content, $component) < 1) {
        continue;
    }
    $newContent = preg_replace($pattern, '$1$2"' . $version . '"', $content);
    if ($mode == 'test') {
        echo $composer, "\n";
        echo $newContent, "\n\n";
    } else {
        file_put_contents($composer, $newContent);
    }
}