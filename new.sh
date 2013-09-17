if [ ! -f /usr/local/bin/composer ]; then
    echo "Composer not installed in: /usr/local/bin/composer";
    exit;
fi

if [ ! -f /usr/local/bin/php ]; then
    echo "PHP not installed in: /usr/local/bin/php";
    exit;
fi

if [ $# != 1 ]; then
	echo "Must provide project name as an argument";
	exit;
fi

path=$PWD"/"$1;

if [ -f $path ]; then
	echo "Project path already exists";
	exit;
fi

if [ ! -w "." ] ; then 
	echo "You do not have write permissions to this folder: "$PWD;
	exit; 
fi

mkdir $path;

echo '{
    "name": "'$1'",
    "minimum-stability": "dev",
    "require": {
        "virtuecenter/framework": "dev-master"
    }
}' > $path/composer.json

cd $path && /usr/local/bin/composer install

echo "<?php
date_default_timezone_set('America/New_York');
require 'vendor/autoload.php';
Framework::route();" > $path/index.php

cd $path && /usr/local/bin/php $path/index.php build

echo 'Your project has been built.
To work local, add the project as a vhost on your webserver.
Then, update your /etc/hosts file.
Do not forget to restart your webserver.
And also to update your config/db.php file.
Happy developing!';