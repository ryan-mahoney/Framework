Concepts
========

Build and Re-Build
++++++++++++++++++

Making a modern website or mobile-website can be complicated, and as such, folder structures have become more and more cluttered.  FMF provides a facilty for builing projects initially, which is to basically go into your projects root folder, and run the following command:

.. code-block:: bash

   mkdir myproject
   php public/index.php build

-------

Also, FMF build a variety of caches that the build process also handles.  So, whenever you add or changes config, collections, forms, filter or subscribers, you will also need to run that commond.  It may seem tedious, but it runs fast and it's a heck of a lot better to have a build process and a fast website than no build process and a slow website.  

In the future, the build process may also work with grunt to minify JS/CSS and possibly build the project as a phar for easy distribution and deployment.

Auto-Generated Routes
+++++++++++++++++++++

API Centric
+++++++++++

Service Oriented
++++++++++++++++

Less-Logic Templates
++++++++++++++++++++

PubSub Decoupling
+++++++++++++++++

No-SQL
++++++

Search
++++++

Queues
++++++