#!/usr/bin/env bash

# add user
sudo usermod -a -G www-data vagrant
sudo usermod -a -G vagrant www-data

# additional repositories
sudo wget -q - https://download.elastic.co/elasticsearch/elasticsearch/elasticsearch-1.5.2.deb
sudo dpkg -i ./elasticsearch-1.5.2.deb
sudo rm ./elasticsearch-1.5.2.deb

# update
sudo apt-get update 2> /dev/null

# install software
sudo apt-get install -y wget node npm libyaml-dev curl make autoconf vim nano mlocate nginx openssl default-jre mongodb-server git memcached libmemcached-tools beanstalkd php5 php5-cli php5-fpm php-pear php5-dev php5-gd php5-curl php5-mcrypt php5-curl php5-tidy php5-mongo php5-memcache 2> /dev/null

# php configuration
sudo pecl install yaml
sudo echo "extension=yaml.so" | sudo tee -a /etc/php5/fpm/php.ini
sudo php5enmod mcrypt

# php composer
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# nginx configuration
sudo sed -i 's/www\-data/vagrant/' /etc/nginx/nginx.conf
sudo sed -i 's/www\-data/vagrant/' /etc/php5/fpm/pool.d/www.conf
mkdir /var/www/storage
mkdir /var/www/imagecache
chown vagrant /var/www --recursive
chgrp vagrant /var/www --recursive
sudo ln -s /var/www/project/server/local.vhost.conf /etc/nginx/sites-enabled/vhost.conf
sudo /etc/init.d/nginx restart
sudo /etc/init.d/elasticsearch start
sudo /etc/init.d/beanstalkd start
sudo service mongodb restart
sudo killall php5-fpm
sudo /etc/init.d/php5-fpm start
