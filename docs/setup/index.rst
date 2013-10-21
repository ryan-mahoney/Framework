Installation
============

System Requirements
+++++++++++++++++++

*For Theming only:*

* PHP 5.4
* PHP Composer
* Local Web Server
  Nginx or Apache
* PHP Modules:
  mbstring, openssl

*For Full-Stack:*

* Memcached
* MongoDB

* Solr
* Beanstalkd
* PHP Modules:
  memcache, gd, mongo, yaml


Creating A Project
++++++++++++++++++

make a new folder, go into it, run PHP, compose... and build

.. code-block:: bash

   mkdir myproject
   cd myproject
   php -r "eval(file_get_contents('https://raw.github.com/virtuecenter/framework/master/project.php'));"
   composer install
   php public/index.php build

For Nginx
+++++++++

For Apache
++++++++++