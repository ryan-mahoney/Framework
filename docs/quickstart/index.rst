Quick Start Guide
=================

This guide covers the basic steps to get your local environment ready so you can start working on a project as quickly as possible.

Obtain & Run Virtual Server
+++++++++++++++++++++++++++

You could setup an entire development environment with a web server, database server, search server, etc – but the fastest way to get started is just to use the pre-configured server appliance.  The files and directions are here :ref:`appliance`


Existing Project: git pull the Project to the Shared Drive
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


If a project has already been created, just pull it down to the shared drive. Most projects will be stored in Git, if you don't know Git – learn it.  The shared drive will be mapped on Windows computers as *//fmf/www* each website project should be saved here. Restart the appliance whenever you install a new website project.


New Project: Build from Scratch
+++++++++++++++++++++++++++++++

If you are building a brand new project from scratch, follow these directions: :ref:`create`


Compose Project Or Download Vendors Folder
++++++++++++++++++++++++++++++++++++++++++

Modern PHP applications obtain their dependencies via Composer.  Composer puts all the dependencies in a “vendors” folder and creates an autoloader.  You can run Composer either locally or on the virtual server from within the project simply by running the command below. 

.. code-block:: bash

   cd /var/www/myproject
   composer install

If for some reason you can not run Composer, you could download a pre-compose vendors folder from `here <http://virtuecenter-fmf.s3.amazonaws.com/vendor.zip>`_, but composing on your own project is always better.


Import Project Data
+++++++++++++++++++

If your project is pre-built, it may need to have it's data dump installed.  You will need to extract the data dump, enter the dump folder, list the folder to see the database name, and run mongorestore to import the data.

.. code-block:: bash

   tar xzf ./dump.tar.gz
   cd dump
   ls
   mongorestore dbname ./dbname


Verify in Web Browser
+++++++++++++++++++++

When you login to your virtual server, it tells you what it's IP is.  If you have installed a new project and re-started Nginx (or the server itself), then going to that IP address in your local web browser should bring up the project.


Write Code
++++++++++

Good luck!
