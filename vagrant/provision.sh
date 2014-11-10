#!/usr/bin/env bash

# add user
sudo usermod -a -G www-data vagrant
sudo usermod -a -G vagrant www-data

# additional repositories
sudo wget -qO - http://packages.elasticsearch.org/GPG-KEY-elasticsearch | sudo apt-key add - 2> /dev/null
sudo echo "deb http://packages.elasticsearch.org/elasticsearch/1.3/debian stable main" | sudo tee -a /etc/apt/sources.list 2> /dev/null

# update
sudo apt-get update 2> /dev/null

# install software
sudo apt-get install -y wget curl make autoconf vim nano mlocate nginx openssl default-jre mongodb-server elasticsearch git memcached libmemcached-tools beanstalkd php5 php5-cli php5-fpm php-pear php5-dev php5-gd php5-curl php5-mcrypt php5-curl php5-tidy php5-mongo php5-memcache 2> /dev/null

# php configuration
sudo pecl install yaml
sudo echo "extension=yaml.so" | sudo tee -a /etc/php5/fpm/php.ini
php5enmod mcrypt

# php composer
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# nginx configuration
sed -i 's/www\-data/vagrant/' /etc/nginx/nginx.conf
sed -i 's/www\-data/vagrant/' /etc/php5/fpm/pool.d/www.conf
mkdir /var/www/storage
mkdir /var/www/imagecache
chown vagrant /var/www --recursive
chgrp vagrant /var/www --recursive
sudo ln -s /var/www/project/server/vhost.conf /etc/nginx/sites-enabled/vhost.conf
sudo /etc/init.d/nginx restart
sudo /etc/init.d/elasticsearch start
sudo /etc/init.d/beanstalkd start
sudo /etc/init.d/memcached start