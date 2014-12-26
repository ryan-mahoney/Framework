#!/usr/bin/env php
<?php
if (!isset($argv[1])) {
    die('need you git API token' . "\n");
}
$token = $argv[1];
$file = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
$root = str_replace('/framework/bin', '', __DIR__);
foreach ($file['require'] as $require => $version) {
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
    //echo '"opine/', $folder, '": "' . $version . '",', "\n";
    $cmd = 'cd ' . $root . ' && ./framework/bin/depver.sh "opine/' . $folder . '" "' . $version . '" write';
    shell_exec($cmd);
    $composerPath = $root . '/' . $folder . '/composer.json';
    composerVersion($composerPath, $version);
    commit($folderPath, $version);
    gitRelease($folderPath, $version, $token);
}

function composerVersion ($path, $version) {
    $file = trim(file_get_contents($path));
    if (substr_count($file, 'version') > 0) {
        $pattern = '/("version")((?: *)\:(?: *))("[\.0-9\-a-z]*")/';
        $file = preg_replace($pattern, '$1$2"' . $version . '"', $file);
    } else {
        $file = substr($file, 0, -2);
        $file = $file . ',' . "\n"    . '    "version": "' . $version . '"' . "\n" . '}';
    }
    file_put_contents($path, $file);
}

function commit ($folderPath, $version) {
    shell_exec('cd ' . $folderPath . ' && git add -u');
    shell_exec('cd ' . $folderPath . ' && git add . ');
    shell_exec('cd ' . $folderPath . ' && git commit -m "Upgrate to ' . $version . '"');
    shell_exec('cd ' . $folderPath . ' && git push origin master');
}

function gitRelease ($path, $version, $token) {
    $cmd = 'cd ' . $path . ' && git remote -v';
    $repo = explode("\n", trim(shell_exec($cmd)))[0];
    $repo = str_replace([' (fetch)', ' (push)', '.git', 'origin' . "\t" . 'git@github.com:'], '', $repo);
    $cmd = 'curl --data \'{"tag_name": "v' . $version . '","target_commitish": "master","name": "v' . $version . '","body": "Release of version ' . $version . '","draft": false,"prerelease": false}\' https://api.github.com/repos/' . $repo . '/releases?access_token=' . $token;
    shell_exec($cmd);
}