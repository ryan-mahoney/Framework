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
        "opine/framework": "dev-master"
    }
}');
} else {
    echo 'composer.json already exists.', "\n\n";
}

echo 'Installing dependencies with composer.', "\n\n";
flush();
passthru('composer install');

if (!file_exists('public')) {
    mkdir('public');
}
if (!file_exists('public/index.php')) {
file_put_contents('public/index.php', '<?php
date_default_timezone_set(\'America/New_York\');
require __DIR__ . \'/../vendor/autoload.php\';
(new Opine\Framework())->frontController();');
} else {
    echo 'public/index.php already exists.', "\n";    
}

if (!file_exists('.gitignore')) {
file_put_contents('.gitignore', 'composer.lock
vendor
managers/cache.json
public/helpers/_build.php
public/css/fields
public/js/fields
acl/_build.json
subscribers/_build.php
bundles/cache.json
collections/cache.json
filters/cache.json
forms/cache.json
managers/cache.json
public/storage
public/layouts/Manager
public/partials/Manager
public/css/Manager
public/js/Manager
public/fonts/Manager
public/layouts/Manager
public/images/Manager
public/helpers/Manager
public/imagecache
public/storage');
}

$root = getcwd();
echo 'Cloning dependency contiainer...', "\n\n";
file_put_contents('container.yml', file_get_contents('vendor/opine/build/static/container.yml'));

echo 'Building project...', "\n\n";
flush();
passthru('php public/index.php build');

echo 'Building complete.', "\n\n";

echo 'Webserver config: (NGINX server block) ', "\n\n", file_get_contents('vendor/opine/build/static/nginx.conf'), "\n\n- - - - - -\n\n";
echo 'Webserver config: (APACHE .htaccess file) ', "\n\n", file_get_contents('vendor/opine/build/static/apache.conf'), "\n\n";

echo 'If you are working locally do not forget to add the host name to your /etc/hosts file and restart your webserver.', "\n\n";

echo 'Happy coding!', "\n";