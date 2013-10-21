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


These files are written in the YAML language.

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

You can read more about this in the *Separation* component.

---------

collections
+++++++++++

The collections folder stores *collection* files that are each one small class for each data collections that will be used in the project.  They are used by to auto-generate APIs and routes.  Here is an example of a blogs collection file:

.. code-block:: php

  <?php
  class blogs {
      use Collection\Collection;
      public $publishable = true
      public static $singular = 'blog';
  }

You can read more about this in the *Collection* component.  FMF comes with many pre-defined collections for popular data types like blogs, pages, videos, photo galleries, etc.

---------

config
++++++

The config folder stores indivdual component configurations, such as the database config file:

.. code-block:: php
  <?php
  return [
      'name' => 'db',
      'conn' => 'mongodb://user:pass@localhost/db',
      'dataAPI'  => 'http://json.virtuecenter.com'
  ];

---------

filters
+++++++

The filters file stores output filters that can be used to perform string replacements on the output just before it is returned to the client.

.. code-block:: php

  <?php
  return function (&$html) {

  $tracking = <<<'TRACKING'
  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-XXXXX-X']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>
  </body>
  TRACKING;

      $html = str_replace('</body>', $tracking, $html);
  };

----------

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