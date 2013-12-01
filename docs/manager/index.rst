Manager
=======

Every website needs a content management system.

---------

background
++++++++++

The FMF framework has a capacity to add "bundles" to an existing project.  Semantic-CM is an FMF based content management system that can be added as a bundle.

---------

installation
++++++++++++

In order to install the manager, you first have an an FMF project.

There are essentially two ways to install the manager.

installing as a download
************************

You can download the manager from Github, and move the "Manager" folder into your project's "bundles" folder.

Here is the `Download Package <https://github.com/virtuecenter/manager/archive/master.zip>`_.

installing as a symbolic link
*****************************

You can clone the manager from Github, and then symbolically link it into your projects "bundles" folder.

This approach is recommended if you are doing any development on the manager itself.

Here is the `Github Clone URL <https://github.com/virtuecenter/manager.git>`_.

One common way to do this is:

.. code-block:: bash

   cd /var/www
   git clone https://github.com/virtuecenter/manager.git
   ln -s /var/www/manager/src/Manager /var/www/myproject/bundles/
   cd /var/www/myproject
   php public/index.php build


The above assumes that you have a a project names "myproject" installed in your /var/www folder.

-----------

installing managers and collections
+++++++++++++++++++++++++++++++++++

Semantic-CM has a library of available managers (and collections) you will need.  Simply copy them into your project's "managers" and "collections" folders, re-build your project and you are good to go.

Test them by going to the "/Manager" path of your project to login.

The default login is admin@website.com : password

Available Managers:
`Manager List <https://github.com/virtuecenter/manager/tree/master/available>`_

Available Collections:
`Collection List <https://github.com/virtuecenter/collection/tree/master/available>`_
