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

The filters folder stores output filters that can be used to perform string replacements on the output just before it is returned to the client.

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

The forms folder contains PHP configuration classes for each of the forms on the website.  Theses files are used to generate user-friendly forms and automate the work of validating, sanitizing and saving data.


.. code-block:: php

	<?php
	class contact {
		public function __construct ($field) {
			$this->field = $field;
		}
		public $storage = [
			'collection'	=> 'contacts',
			'key'			=> '_id'
		];
		public $after = 'notice';
		public $notice = 'Your contact request has been received';
		public $noticeDetails = 'We will be in touch soon.';

		function first_nameField() {
			return [
				'name'		=> 'first_name',
				'placeholder' => 'First Name',
				'display'	=> $this->field->inputText(),
				'required' 	=> true
			];
		}
		
		function last_nameField() {
			return [
				'name'		=> 'last_name',
				'placeholder' => 'Last Name',
				'label'		=> 'Last Name',
				'display'	=> $this->field->inputText(),
				'required'	=> true
			];
		}

		function phoneField() {
			return [
				'name'		=> 'phone',
				'placeholder'	=> 'Phone',
				'display'	=> $this->field->inputText(),
				'required'	=> true
			];
		}
		
		function emailField() {
			return [
				'name'		=> 'email',
				'placeholder'		=> 'Email Address',
				'display'	=> $this->field->inputText(),
				'required'	=> true
			];
		}
		
		function messageField() {
			return [
				'name'		=> 'message',
				'placeholder'		=> 'Enter your message here',
				'display'	=> $this->field->textarea(),
				'required'	=> true
			];
		}
	}

----------

public
++++++

The public folder stores files that should be publicly available on the website such as css and js files, as well as some files in the "layouts", "partials" and "helpers" folder that are used for front-end theming.

public/index.php
****************

The index.php is an auto-generated bootstrap file that includes the auto-loader and calls the FMF front Controller.

.. code-block:: php

   <?php
   date_default_timezone_set('America/New_York');
   require '../vendor/autoload.php';

   (new Framework\Framework())->frontController();

---------

public/css, public/fonts, public/images, public/js
**************************************************

Self explanatory.

---------

public/helpers
**************

The helpers folders stores "helper" files that help the less-logic templateing enginer (Handlebars) do more logic.  This should be used sparingly, but is good for things like pagination which might be hard to achieve in Handlebars without a help.

----------

public/layouts
**************

The layouts folder stores individual *layouts* are the outer-HTML of a single-page application that the partials are rendered into.  For example:

.. code-block:: html
    <!DOCTYPE html>
	<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>{{title}}</title>
	</head>
	<body>
		<div class="wrapper">
	  		{{{header}}}
	  		<div class="container main_container">
	    		<div class="content_left">
	    			{{{pages}}}
	    		</div>
	    		<div class="content_right">
	    			{{{twitter}}}
	    		</div>
	    		<div class="clear"></div>
	  		</div>
	  		{{{footer}}}
		</div>
	</body>
	</html>

In the above example, things like {{{header}}} will be substituted with the markup for the header.

---------

public/partials
***************

subscribers
+++++++++++

vendors
+++++++

FMF relies heavily on PHP composer.  The vendors folder is where composer stores all the various dependecies it compiles and the auto-loader.