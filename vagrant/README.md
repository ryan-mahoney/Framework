# Opine-PHP on Vagrant

```text
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo apt-get install virtualbox
wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.6.5_x86_64.deb
sudo dpkg -i vagrant_1.6.5_x86_64.deb
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/Vagrantfile
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/provision.sh
mkdir project
mkdir project/public
mkdir project/server
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/composer.json ./project/composer.json
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/index.php ./project/public/index.php
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/includes/vhost.conf ./project/server/host.conf
cd project
composer install
echo 100000|sudo tee /proc/sys/fs/inotify/max_user_watches
cd ..
vagrant plugin install vagrant-gatling-rsync
vagrant gatling-rsync-auto
vagrant up
```
