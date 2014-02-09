Forms
=====

For a website or mobile web application to collect information, it must contain forms.

While forms may seem like a mundane topic, they can only be ignored at the detriment of a successful website project.

Forms must encompass the important fields and be visually layed out in a clear manner and function properly.

Overview
++++++++

Creating a form with Opine-PHP involves at least 4 files: a confguration file, a layout, a partial and a form class.

The files can be found:

- /app/forms/example.yml
- /forms/example.php 
- /public/layouts/forms/example.html
- /public/partials/forms/example.hbs

This form will be automatically routes to:

/form/example

The Application Config
++++++++++++++++++++++

.. code-block:: yaml

  binding:
      example:
          url: '%dataAPI%/json-form/example'
          args: []
          partial: 'forms/example.hbs'
          type: "Form"


----------------------------

The Layout
++++++++++


.. code-block:: html

  <!DOCTYPE html>
  <html>
      <head>
          <title>Example</title>
      </head>
      <body>
          {{{example}}}
      </body>
  </html>


----------------------------

The Partial
+++++++++++

.. code-block:: html

  <form method="post" data-xhr="true">
    {{{email}}}
    {{{id}}}
  </form>


----------------------------

The Form Class
++++++++++++++

.. code-block:: php

  <?php
  namespace Form;

  class example {
      public function __construct ($field) {
          $this->field = $field;
      }

      public $storage = [
          'collection'    => 'example',
          'key'            => '_id'
      ];
      public $after = 'redirect';
      public $redirect = '/';

      function emailField() {
          return [
              'name' => 'email',
              'placeholder' => 'Email',
              'display' => 'InputText',
              'required' => true
          ];
      }
  }


There are 3 possible options for $after: redirect, notice, or function.  

"redirect" send the user to another URL.  

"notice" displays a message. 

"function" calls a predefined function in the page with the following options: form, submittedData, response


----------------------------

Saving Data
+++++++++++

The above code will put a form on your page.  But to have the data actually save to the database, you need a subscriber to subscribe to it's document.  For example:


In your /subscribers/topics.yml:

.. code-block:: yaml

  topics:
      form-example-save: 
         PostToDB: ['post', 'db']


"PostToDB" is a bult in subscriber, but if you want to see it's code:

.. code-block:: php

  <?php
  return function ($context, $post, $db) {
      if (!isset($context['dbURI']) || empty($context['dbURI'])) {
          throw new \Exception('Event does not contain a dbURI');
      }
      if (!isset($context['formMarker'])) {
          throw new \Exception('Form marker not set in post');
      }
      $document = $post->{$context['formMarker']};
      if ($document === false || empty($document)) {
          throw new \Exception('Document not found in post');
      }
      $documentObject = $db->documentStage($context['dbURI'], $document);
      $documentObject->upsert();
      $post->statusSaved();
  };


Javascrpt Files to Include
++++++++++++++++++++++++++

Opine-PHP depends on the following files being included in order for forms to properly be submitted:

*Note: You can more or less use any version of jquery, but don't include it twice*

*Note: all of the form files are automatically copied into your project's "/public/js" folder on build.

.. code-block:: httml

  <script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="/js/jquery.form.js"></script>
  <script type="text/javascript" src="/js/formXHR.js"></script>
  <script type="text/javascript" src="/js/formHelperSemantic.js"></script>


Common Errors
+++++++++++++

1. Your form's partial should include an "id slot", {{{id}}}
2. Your form needs to actually be submitted, so you will need an <input type="submit" /> or other common form submission method.
3. check your Firebug or other AJAX console if your form submission fails
4. If your form does not submit via ajax, check your javascript paths 
5. Don't forget to put data-xhr="true" in your form tag
