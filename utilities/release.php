<?php
foreach (new DirectoryIterator(__DIR__ . '/../vendor/opine') as $fileInfo) {
    if ($fileInfo->isDot()) continue;
    if (!$fileInfo->isDir()) continue;
    $hash = trim(shell_exec('cd ' . __DIR__ . '/../vendor/opine/' . $fileInfo->getFilename() . ' && git rev-parse HEAD'));
    echo '"opine/', $fileInfo->getFilename(), '": "dev-master#', $hash, '",', "\n";
}