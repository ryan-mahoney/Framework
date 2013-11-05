Server Appliance
================

FMF does not compromise on using the latest server technologies and platforms.  As such, FMF has many dependencies, some of which are hard to install and configure.  

A VirtualBox appliance has been created which bundles all of the dependencies into one file.  This document will show you how to install your own local server appliance so you can quickly begin developing websites on this advanced platform.

.. _appliance:

Installing VirtualBox
+++++++++++++++++++++

If you are not familiar with Virtualization, it is basically a way to run one or more operating systems inside another.  In our case, we are using the free Virtualization "Hypervisor" VirtualBox which is now owned by Oracle.

To download VirtualBox for any platform, `go to this link <https://www.virtualbox.org/wiki/Downloads>`_. 

Once downloaded, follow the installation steps for your platform, it is easy to install.


Obtaining the Appliance
+++++++++++++++++++++++

To run the FMF server, you will need to download and import the server appliance.  You can `download it here <http://virtuecenter-fmf.s3.amazonaws.com/fmf.ova>`_.  It is a large file, a little over 1GB.


Using the Appliance
+++++++++++++++++++

Using the appliance is a simple as importing it in VirtualBox and clicking the start button.  A window will pop-up and your web server will be running.

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/virtualbox.PNG

The first time you run it, it may ask you to update the network settings.  

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/002-change-network.PNG

Make sure you are using "Bridged" networking, and choose your network adapter -- it should be automatically selected.  

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/003-Brindge.PNG

The virtual server will obtain a new IP from your router.


Shared Drive
++++++++++++

In order to allow you to work from your "host" machine, but view files through the server appliance, a shared directory is automatically created that makes your /var/www folder accessible.

If you are using Windows, you should automatically see a new shared drive appear named FMF.  Putting a folder in this directory makes it accessible to your web server.  For example, if you had a project called "myproject", it would be pulled from git such that it was in the "/var/www/myproject" folder on Linux and the "//fmf/www/myproject" folder on Windows. Each project contains a vhost.conf file that Nginx reads to establish the VHOST for the project.  Nginx expects all VHOSTS to be in the directory structure of "/var/www/myproject/vhost.conf".

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/006-WindowsAccess.PNG

Demo Project
++++++++++++

The Appliance comes pre-installed with the demo project running on port 81.  In order to view the demo project, you will need to know the dynamically assigned IP address of your Virtual Server.  Determining the IP address is easy.  You can login to the server with the user account *fmf* and the password *fmf*.  Also, the super user account's password is also *fmf*, in-case you need it.  The IP address is displayed when you login.

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/004-login.PNG

Below, the IP address has been typed into the web browser on the host machine with the port # of 81. The demo site is displayed.

.. image:: https://raw.github.com/virtuecenter/framework/master/docs/appliance/images/005-demosite.PNG

New Project
+++++++++++

When you install a new project, it will be configured on port 80 so as not to conflict with the demo project.  Unless you map host names to the IP, you will only be able to work on one project at a time, or you can shift each project to a different port number.

When you first install a new project with a new vhost file, the web server will not be aware of it.  You can either restart Nginx itself, or restart the appliance.

To restart Nginx:

.. code-block:: bash

   sudo /etc/init.d/nginx restart

You will be prompted for the super-user password, which is *FMF*
