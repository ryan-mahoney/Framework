if (!is_writable(__DIR__)) {
	echo 'You need write permissions for this directory.', "\n";
	exit;
}
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    echo 'You need at least PHP version 5.4.0, your version us: ', PHP_VERSION, "\n";
    exit;
}
if (!file_exists('composer.json')) {
file_put_contents('composer.json', '{
    "name": "project",
    "minimum-stability": "dev",
    "require": {
        "virtuecenter/framework": "dev-master"
    }
}');
} else {
	echo 'composer.json already exists.', "\n";
}

echo 'Installing dependencies with composer.', "\n";
flush();
passthru('composer install');

if (!file_exists('index.php')) {
file_put_contents('index.php', '<?php
date_default_timezone_set(\'America/New_York\');
require \'vendor/autoload.php\';
Framework\Framework::route();');
} else {
	echo 'index.php already exists.', "\n";	
}

echo 'Building project...', "\n";
flush();
passthru('php index.php build');

echo 'Project Built.', "\n";

echo 'FOR NGINX: ', "\n\n", file_get_contents('vendor/virtuecenter/build/static/nginx.conf'), "\n\n- - - - - -\n\n";
echo 'FOR APACHE: ', "\n\n", file_get_contents('vendor/virtuecenter/build/static/apache.conf'), "\n";