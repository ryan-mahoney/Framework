# PHP FMF

## PHP Federated Micro Framework

RESTful, Event-Driven, NoSQL PHP Framework... based on micro frameworks.

This framework is installed via Composer.  

**Requires PHP 5.4.**  

**PHP Minimum Module Dependencies:**  
*mbstring, memcache, openssl*

**System Support:**  
*Linux / Windows / Mac*

**Server Process Dependecies**  
*Memcached*

**Extra Dependecies / For DB Interaction**   
(not for working Local)  
*MongoDB, Beanstalkd, Solr*

- - -

This framework aims to use the latest PHP technology for high-speed execution, but a design that is extremely modular and minimal while achieving maximum unit-testability.  

to install, from withing a new folder:  

```bash
php -r 'eval(str_replace("<?php", "", file_get_contents("https://raw.github.com/virtuecenter/framework/master/project.php")));'
```

If your project already exits, you will just need to obtain it via git, and build it with composer.  

```bash
composer install
```

periodically, you will need to re-build the project as you add more forms, collections, helpers, filters, etc.  
```bash
php index.php build
```