#!/usr/bin/env bash

# add user
sudo usermod -a -G www-data vagrant
sudo usermod -a -G vagrant www-data

# additional repositories
sudo wget -qO - http://packages.elasticsearch.org/GPG-KEY-elasticsearch | sudo apt-key add - 2> /dev/null
sudo echo "deb http://packages.elasticsearch.org/elasticsearch/1.3/debian stable main" | sudo tee -a /etc/apt/sources.list 2> /dev/null

# update
sudo apt-get update 2> /dev/null

# install services
sudo apt-get install -y wget curl 2> /dev/null
sudo apt-get install -y make autoconf 2> /dev/null
sudo apt-get install -y vim 2> /dev/null
sudo apt-get install -y nano 2> /dev/null
sudo apt-get install -y mlocate 2> /dev/null
sudo apt-get install -y nginx 2> /dev/null
sudo apt-get install -y openssl 2> /dev/null
sudo apt-get install -y default-jre 2> /dev/null
sudo apt-get install -y mongodb-server 2> /dev/null
sudo apt-get install -y elasticsearch 2> /dev/null
sudo apt-get install -y git 2> /dev/null
sudo apt-get install -y memcached libmemcached-tools 2> /dev/null
sudo apt-get install -y beanstalkd 2> /dev/null
sudo apt-get install -y libmcrypt-dev libxml2-dev libtidy-dev libzip-dev libgd2-xpm-dev libcurl4-openssl-dev libyaml-dev libevent-dev 2> /dev/null
sudo apt-get install -y php5 php5-cli php5-fpm php-pear php5-dev php5-gd php5-curl php5-mcrypt php5-curl php5-tidy 2> /dev/null

sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password ROOTPASSWORD' 2> /dev/null
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password ROOTPASSWORD' 2> /dev/null
sudo apt-get install -y mysql-server 2> /dev/null
sudo apt-get install -y mysql-client 2> /dev/null

# mysql configuration
if [ ! -f /var/log/dbinstalled ];
then
    echo "CREATE USER 'mysqluser'@'localhost' IDENTIFIED BY 'USERPASSWORD'" | mysql -uroot -pROOTPASSWORD
    echo "CREATE DATABASE internal" | mysql -uroot -pROOTPASSWORD
    echo "GRANT ALL ON internal.* TO 'mysqluser'@'localhost'" | mysql -uroot -pROOTPASSWORD
    echo "flush privileges" | mysql -uroot -pROOTPASSWORD
fi

# php configuration
printf "\n" | sudo pecl install yaml mongo
yes | sudo pecl install memcache 2> /dev/null
sudo echo "extension=memcache.so" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "memcache.hash_strategy=\"consistent\"" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "extension=yaml.so" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "extension=mongo.so" | sudo tee -a /etc/php5/fpm/php.ini

sudo echo "opcache.enable=1" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "opcache.memory_consumption=128" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "opcache.max_accelerated_files=4000" | sudo tee -a /etc/php5/fpm/php.ini
sudo echo "opcache.revalidate_freq=60" | sudo tee -a /etc/php5/fpm/php.ini

# php composer
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# nginx configuration
sudo mkdir /var/www
sudo ln -s /var/www/project/server/vhost.conf /etc/nginx/sites-enabled/vhost.conf
sudo /etc/init.d/nginx restart

# opine cli tools