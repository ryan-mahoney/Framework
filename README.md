# PHP FMF

## PHP Federated Micro Framework

RESTful, Event-Driven, NoSQL PHP Framework... based on other small micro frameworks.

This framework is based on Slim and is installed via Composer.  

**Requires PHP 5.4.**  

**PHP Module Dependencies:**  
*mbstring, memcache, openssl*

**System Support:**  
*Linux*

**Server Process Dependecies**  
*Memcached*

**Extra Dependecies / For Production Server**   
(not for working Local)  
*MongoDB, Beanstalkd, Solr*

- - -

This framework aims to use the latest PHP technology for high-speed execution, but a design that is extremely modular and minimal while achieving maximum unit-testability.

to install (change ``projectFolderName`` to the name of your project):

```bash
wget https://raw.github.com/virtuecenter/framework/master/new.sh -v -O new.sh && chmod +x ./new.sh && ./new.sh projectFolderName; rm -rf new.sh
```

If your project already exits, you will just need to obtain it via git, and build it with composer.  

```bash
composer install
```

periodically, you will need to re-build the project as you add more forms, collections, etc
```bash
php index.php build
```
