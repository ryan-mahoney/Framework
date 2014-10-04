# Opine-PHP on Vagrant

```text
sudo apt-get install virtualbox
wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.6.5_x86_64.deb
sudo dpkg -i vagrant_1.6.5_x86_64.deb
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/Vagrantfile
wget https://raw.githubusercontent.com/Opine-Org/Framework/master/vagrant/provision.sh
mkdir project
mkdir project/public
mkdir project/server
vagrant up
vagrant plugin install vagrant-gatling-rsync
echo 100000|sudo tee /proc/sys/fs/inotify/max_user_watches
vagrant gatling-rsync-auto
```
