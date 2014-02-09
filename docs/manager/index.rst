Manager
=======

Every website needs a content management system.

---------

Background
++++++++++

The Opine-PHP has a capacity to add "bundles" to an existing project.  Semantic-CM is an Opine-PHP based content management system that can be added as a bundle.

---------

Installation
++++++++++++

In order to install the manager, you first have an an Opine-PHP project.

There are essentially two ways to install the manager.

Installing as a Download
************************

You can download the manager from Github, and move the "Manager" folder into your project's "bundles" folder.

Here is the `Download Package <https://github.com/Opine-Org/Semantic-CM/archive/0.9.tar.gz>`_.

Installing as a Symbolic Link
*****************************

You can clone the manager from Github, and then symbolically link it into your projects "bundles" folder.

This approach is recommended if you are doing any development on the manager itself.

Here is the `Github Clone URL <https://github.com/Opine-Org/Semantic-CM.git>`_.

One common way to do this is:

.. code-block:: bash

   cd /var/www
   git clone https://github.com/Opine-Org/Semantic-CM.git
   ln -s /var/www/Semantic-CM/src/Manager /var/www/myproject/bundles/
   cd /var/www/myproject
   php public/index.php build


The above assumes that you have a a project names "myproject" installed in your /var/www folder.

-----------

Installing Individual Managers and Collections
++++++++++++++++++++++++++++++++++++++++++++++

Semantic-CM has a library of available managers (and collections) you will need.  Simply copy them into your project's "managers" and "collections" folders, re-build your project and you are good to go.

Test them by going to the "/Manager" path of your project to login.

The default login is admin@website.com : password

Available Managers:
`Manager List <https://github.com/Opine-Org/Semantic-CM/tree/master/available>`_

Available Collections:
`Collection List <https://github.com/Opine-Org/Collection/tree/master/available>`_
