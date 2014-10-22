<?php
foreach (new DirectoryIterator('./') as $fileInfo) {
    if ($fileInfo->isDot()) continue;
    if (!$fileInfo->isDir()) continue;
    $hash = trim(shell_exec('cd ./' . $fileInfo->getFilename() . ' && git rev-parse HEAD'));
    echo '"opine/', $fileInfo->getFilename(), '": "dev-master#', $hash, '",', "\n";
}
