Installation
============

System Requirements
+++++++++++++++++++

**For Project Theming only:**

* PHP 5.4
* PHP Composer
* Local Web Server
  Nginx or Apache
* PHP Modules:
  mbstring, openssl

*For Full-Stack Development:*

* Memcached
* MongoDB
* Solr
* Beanstalkd
* PHP Modules:
  memcache, gd, mongo, yaml

Docker
******

A version of the system for Docker.io will be available soon.

Creating A Project
++++++++++++++++++

Open your command line terminal.  Enter the following commands to make a new folder, go into it, download the framework, and compose / build it.

.. code-block:: bash

   mkdir myproject
   cd myproject
   php -r "eval(file_get_contents('https://raw.github.com/virtuecenter/framework/master/project.php'));"
   composer install
   php public/index.php build

For Nginx & PHP-FPM
+++++++++++++++++++

This is the recommended web-server.

.. code-block:: bash

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


For Apache
++++++++++

Add the following logic to your .htaccess files:

.. code-block:: bash
   
   Options +FollowSymLinks
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^ index.php [L] 

Windows
+++++++

FMF has been tested on Windows, but it is not tested as often as it is on Linux or MacOS.  Isn't it time to make the switch?