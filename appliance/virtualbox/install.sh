# FMF Environment
#
# VERSION	0.1
# VIRTUALBOX-VERSION	0.6

# Picked Ubuntu 12.04.3 as the Primary OS.
# FROM ubuntu:precise

# Gave myself some credit for doing this. Give it where it's due!
# MAINTAINER Kenny Raghunath <Kenny@masyafstudios.com>, Ryan Mahoney <ryan@virtuecenter.com>

# Create a missing user group and web directory
addgroup nobody
mkdir /var/www
chown nobody /var/www

# Appearantly a requirement that's needed for the next step, also some basic tools to use later on.
apt-get -y install nano wget

# Install all necessary keys for MongoDB. Instructions are found on the official website.
apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | tee /etc/apt/sources.list.d/mongodb.list

# Install Nginx using Launchpad's version of latest stable.
apt-add-repository -y ppa:nginx/stable

# Update all apt repositories to get new sources.
apt-get update

# Install all apt repository software. This includes MongoDB, Memcache, Git, Beanstalkd, and nginx. There are also some other basic system requirements that are needed.
apt-get install -y beanstalkd mongodb-10gen memcached nginx git openjdk-6-jdk libmcrypt-dev libxml2-dev curl libtidy-dev libzip-dev libgd2-xpm-dev libcurl4-openssl-dev autoconf libyaml-dev libevent-dev make

# Install PHP 5.5.5 from source. It is a long command chain due to what I understand of Docker.
wget http://us2.php.net/get/php-5.5.5.tar.gz/from/us1.php.net/mirror -O ./php-5.5.5.tar.gz
tar xzf ./php-5.5.5.tar.gz && cd php-5.5.5/ && ./configure --enable-fpm --with-curl --with-openssl --with-gd --with-tidy --enable-zip --with-mcrypt && make && make install

# composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install the PHP extensions that's needed for FMF. Since some of the pecl requests are interactive, the printf command tells it to default to whatever it has.
printf "\n" | pecl install yaml memcache mongo

# Next we install Elastic Search. I'm not sure if this works out of the box, but through testing the script, we shall see.
wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-0.90.5.deb
dpkg -i elasticsearch-0.90.5.deb

# Expose the ports we need to the outside. [ 11300 is Beanstalkd ]
#EXPOSE 80 11211 27017 11300

# Configure nginx, PHP and other pieces of software for use.
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/nginx.conf -O /etc/nginx/nginx.conf
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/fastcgi_params -O /etc/nginx/fastcgi_params
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/php.ini -O /usr/local/lib/php.ini
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/php-fpm.conf -O /usr/local/etc/php-fpm.conf
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/php-fpm -O /etc/init.d/php-fpm
chmod +x /etc/init.d/php-fpm
update-rc.d php-fpm defaults
wget https://raw.github.com/virtuecenter/framework/master/appliance/virtualbox/config/beanstalkd -O /etc/default/beanstalkd

# Run commands to start up the environment.
/etc/init.d/nginx stop
/etc/init.d/nginx start
/etc/init.d/php-fpm stop
/etc/init.d/php-fpm start
service mongodb stop
service mongodb start
/etc/init.d/beanstalkd stop
/etc/init.d/beanstalkd start
service elasticsearch stop
service elasticsearch start