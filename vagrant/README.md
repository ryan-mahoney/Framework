# Opine-PHP on Vagrant

```text
sudo apt-get install virtualbox
wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.6.5_x86_64.deb
sudo dpkg -i vagrant_1.6.5_x86_64.deb
vagrant init ubuntu/trusty64
vagrant provision
vagrant plugin install vagrant-gatling-rsync
vagrant up
echo 100000|sudo tee /proc/sys/fs/inotify/max_user_watches
vagrant gatling-rsync-auto
```
