Installation
============

System Requirements
+++++++++++++++++++

For Project Theming only:
*************************

* `PHP 5.4 <http://www.php.net/>`_
* `PHP Composer <http://getcomposer.org/>`_
* Local Web Server 
  `Nginx <http://nginx.org/>`_ or `Apache <http://httpd.apache.org/>`_
* PHP Modules: 
  `mbstring <http://php.net/manual/en/book.mbstring.php>`_, `openssl <http://php.net/manual/en/book.openssl.php>`_

For Full-Stack Development:
***************************

* `Memcached <http://memcached.org/>`_
* `MongoDB <http://www.mongodb.org/>`_
* `Solr <http://lucene.apache.org/solr/>`_
* `Beanstalkd <http://kr.github.io/beanstalkd/>`_
* PHP Modules: 
  `memcache <http://pecl.php.net/package/memcache>`_, `gd <http://php.net/manual/en/book.image.php>`_, `mongo <http://pecl.php.net/package/mongo>`_, `yaml <http://pecl.php.net/package/yaml>`_

Docker
******

A version of the system for `Docker <https://www.docker.io/>`_ will be available soon.

-----------

Creating A Project
++++++++++++++++++

.. _create:

Open your command line terminal.  Enter the following commands to make a new folder, go into it, download the framework, and compose / build it.

.. code-block:: bash

   mkdir myproject
   cd myproject
   php -r "eval(file_get_contents('https://raw.github.com/virtuecenter/framework/master/project.php'));"
   composer install
   php public/index.php build

----------

Web Servers
+++++++++++

Nginx & PHP-FPM
***************

Nginx is the recommended web-server for FMF.

.. code-block:: nginx

    server {
      listen       *:80;
      server_name  myrpoject.localhost myproject.com;
      root         /etc/nginx/sites-available/myproject/public;

      try_files $uri /index.php?$query_string;

      location ~ .php$ {
          fastcgi_connect_timeout 3s;
          fastcgi_read_timeout 10s;
          include fastcgi_params;
          fastcgi_pass 127.0.0.1:9000;
      }
  }


Apache
******

Add the following logic to your .htaccess files:

.. code-block:: apache
   
   Options +FollowSymLinks
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^ index.php [L] 

--------

Windows
+++++++

FMF has been tested on Windows, but it is not tested as often as it is on Linux or MacOS.  Isn't it time to make the `switch <http://www.ubuntu.com/>`_?