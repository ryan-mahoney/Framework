Folder Structure
================

A place for everyhing and everything in it's place.

---------

index.php
+++++++++

The index.php is an auto-generated bootstrap file that includes the auto-loader and calls the FMF front Controller.

.. code-block:: php

   <?php
   date_default_timezone_set('America/New_York');
   require '../vendor/autoload.php';

   (new Framework\Framework())->frontController();

app
+++

collections
+++++++++++

config
++++++

filters
+++++++

forms
+++++

public
++++++

css
***

fonts
*****

helpers
*******

images
******

js
**

public/layouts
++++++++++++++

partials
++++++++

subscribers
+++++++++++

vendors
+++++++