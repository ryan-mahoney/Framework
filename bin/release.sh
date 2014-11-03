#!/usr/local/bin/php
<?php
$file = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
$root = str_replace('/framework/bin', '', __DIR__);
foreach ($file['require'] as $require => $value) {
    $tmp = explode('/', $require, 2);
    $namespace = $tmp[0];
    if ($namespace != 'opine') {
        continue;
    }
    $folder = $tmp[1];
    $folderPath = $root . '/' . $folder;
    if (!file_exists($folderPath)) {
        echo 'folder does not exist: ', $folderPath, "\n";
        continue;
    }
    $hash = trim(shell_exec('cd ' . $folderPath . ' && git rev-parse HEAD'));
    echo '"opine/', $folder, '": "dev-master#', $hash, '",', "\n";
}