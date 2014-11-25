#!/usr/bin/env php
<?php
$message = $argv[1];
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
    $status = trim(shell_exec('cd ' . $folderPath . ' && git status'));
    if (substr_count($status, 'nothing to commit, working directory clean') > 0) {
        continue;
    }
    echo $folder, "\n";
    echo shell_exec('cd ' . $folderPath . ' && git add -u && git add . && git commit -m "' . $message . '" && git push origin master'), "\n\n";
}