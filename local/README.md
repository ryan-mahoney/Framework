# Opine-PHP on local Ubuntu

### Project Setup

```text
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
cd /var/www
mkdir project
mkdir project/public
mkdir project/server
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/composer.json ./project/composer.json
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/index.php ./project/public/index.php
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/vhost.conf ./project/server/host.conf
cd project
composer install
```


### Service Setup
```text
# additional repositories
sudo wget -qO - http://packages.elasticsearch.org/GPG-KEY-elasticsearch | sudo apt-key add -
sudo echo "deb http://packages.elasticsearch.org/elasticsearch/1.3/debian stable main" | sudo tee -a /etc/apt/sources.list

# update
sudo apt-get update

# install software
sudo apt-get install -y wget libyaml-dev curl make autoconf vim nano mlocate nginx openssl default-jre mongodb-server elasticsearch git memcached libmemcached-tools beanstalkd php5 php5-cli php5-fpm php-pear php5-dev php5-gd php5-curl php5-mcrypt php5-curl php5-tidy php5-mongo php5-memcache

# php configuration
sudo pecl install yaml
sudo echo "extension=yaml.so" | sudo tee -a /etc/php5/fpm/php.ini
sudo php5enmod mcrypt

# php composer
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# nginx configuration
mkdir /var/www/storage
mkdir /var/www/imagecache
sudo ln -s /var/www/project/server/local.vhost.conf /etc/nginx/sites-enabled/vhost.conf
sudo /etc/init.d/nginx restart
sudo /etc/init.d/elasticsearch start
sudo /etc/init.d/beanstalkd start
sudo service mongodb restart
sudo /etc/init.d/nginx restart
sudo killall php5-fpm
sudo /etc/init.d/php5-fpm start
```
