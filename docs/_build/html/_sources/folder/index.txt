Folder Structure
================

A place for everyhing and everything in it's place.

---------

app
+++

The app folder stores single-page application configurations.  Specifcally, it stores the bindings between:

* the id of the section in layout to populate markup into, in this example: contact, header and footer
* a RESTful API "url"
* "args" to pass to the data URL
* a "partial" template to render the data with
* a built in "type" for type-specific rendering logics


These files are written in the YAML language.  You can read more about this in the *Separation* component.

.. code-block:: yaml

  js:

  binding:
      contact:
          url: '%dataAPI%/json-form/contact'
          args: []
          partial: 'form-contact.hbs'
          type: "Form"
      header:
          url: "%dataAPI%/header"
          type: "html"
      footer:
          url: "%dataAPI%/footer"
          type: "html"

---------

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

index.php
*********

The index.php is an auto-generated bootstrap file that includes the auto-loader and calls the FMF front Controller.

.. code-block:: php

   <?php
   date_default_timezone_set('America/New_York');
   require '../vendor/autoload.php';

   (new Framework\Framework())->frontController();

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

layouts
*******

partials
********

subscribers
+++++++++++

vendors
+++++++