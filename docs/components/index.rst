Components
==========

.. _collection:

Collection
++++++++++

Fork on github here: `collection source <https://github.com/virtuecenter/collection>`_

Collections are at the heart of how Opine-PHP makes data accessable.  Opine-PHP will create RESTful APIs that produce JSON data, as well as front-end routes for collection data on a website front-end.

Collection files are typically small, but they may contain logic for transforming individual documents returned:

.. code-block:: php

	<?php
	use UrlId\UrlId;

	class videos {
		use Collection\Collection;
		public $publishable = true;
		public $singular = 'video';

		public function document (&$document) {
			$document['video_id'] = null;
			$document['video_type'] = null;
			if (!empty($document['video'])) {
				$document['video_id'] = UrlId::parse($document['video'], $document['video_type']);
			}
			$document['category_titles'] = [];
			if (isset($document['categories']) && is_array($document['categories'])) {
				foreach ($document['categories'] as $id) {
					$category = $this->db->collection('categories')->findOne(['_id' => $this->db->id($id)], ['title']);
					if (!isset($category['_id'])) {
						continue;
					}
					$document['category_titles'][] = $category['title'];
				}
			}
		}
	}

----------

An example of the result of a collection can be found here: `Blogs Collection API <http://json.virtuecenter.com/json-data/blogs/all?pretty>`_

Collection APIs are handy, because you can see the field layout easily in JSON:


.. code-block:: json

    {
        "blogs": [
            {
                "_id": "50490cb9b79921001200000b",
                "body": "<p>Article Content</p>",
                "code_name": "naacp_calls_for_an_end_to_birmingham_police_using_pepper_spray_in_schools",
                "comments": "t",
                "description": "Some Descriptive Text"",
                "display_date": {
                    "sec": 1346126400,
                    "usec": 0
                },
                "featured": "f",
                "image": {
                    "name": "us-davis-pepper-spray.jpg",
                    "size": "411507",
                    "type": "image/jpeg",
                    "url": "http://virtuecenter.s3.amazonaws.com/files/2012-09-06-16/us-davis-pepper-spray.jpg",
                    "height": "453",
                    "width": "680"
                },
                "pinned": "f",
                "status": "published",
                "tags": [],
                "title": "NAACP calls for an end to Birmingham police using pepper spray in schools",
                "display_date__MdY": "Aug 28, 2012",
                "path": "/blog/naacp_calls_for_an_end_to_birmingham_police_using_pepper_spray_in_schools.html"
            }
        ],
        "pagination": {
            "limit": 20,
            "total": 1,
            "page": 1,
            "pageCount": 1
        }
    }


---------


The API for working with collection data is restul and follows the follwing format:

http://website.com/json-date/collection/method/limit/page/sortjson

For example:

http://json.virtuecenter.com/json-data/blogs/all/5/0/{"display_date":-1}

The API has many built in methods:

* all
* byAuthorId
* byAuthor
* byCategory
* byCategoryFeatured
* byCategoryId
* byCategoryIdFeatured
* byDatePast
* byDateUpcoming
* byId
* bySlug
* byTag
* byTagFeatured
* featured
* popular


To use a method that takes and argument, hyphenate the argument with the method, for example:

http://json.virtuecenter.com/json-data/blogs/byTag-GovernorBentley


----------


.. form:

Form
++++

Fork on github here: `form source <https://github.com/virtuecenter/form>`_

The Form component is a very advanced form generator that supports rendering a wide variety of field types in a very flexible way.

It is intended to be used with `Semantic UI Form Collection <http://semantic-ui.com/collections/form.html>`_

An individual form class looks like this:

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

It's possible to define custom callbacks for validation and data sanitization.

Under the hood, the system takes information like this, and uses it to form a JSON array of field types that populates a partial.

Note, in the partial below, the form has an attribute set: data-xhr="true"

.. code-block:: html

    <h2 class="ui dividing header">Contact Form</h2>

    <form class="ui form segment" data-xhr="true" data-marker="contact" method="post">
        <div class="ui warning message">
            <div class="header">There was a problem</div>
            <ul class="list">
            </ul>
        </div>

        <div class="field">
            <label>First Name</label>
            <div class="ui left labeled input">
                {{{first_name}}}
                <div class="ui corner label">
                    <i class="icon asterisk"></i>
                </div>
            </div>
        </div>

        <div class="field">
            <label>Last Name</label>
            <div class="ui left labeled input">
                {{{last_name}}}
                <div class="ui corner label">
                    <i class="icon asterisk"></i>
                </div>
            </div>
        </div>

        <div class="field">
            <label>Phone</label>
            <div class="ui left labeled input">
                {{{phone}}}
                <div class="ui corner label">
                    <i class="icon asterisk"></i>
                </div>
            </div>
        </div>

        <div class="field">
            <label>Email</label>
            <div class="ui left labeled input">
                {{{email}}}
                <div class="ui corner label">
                    <i class="icon asterisk"></i>
                </div>
            </div>
        </div>

        <div class="field">
            <label>Message</label>
            <div class="ui left labeled input">
                {{{message}}}
                <div class="ui corner label">
                    <i class="icon asterisk"></i>
                </div>
            </div>
        </div>
        {{{id}}}
        <input type="submit" class="ui blue submit button" value="Submit Contact Form" />
    </form>

A partial like that could be rendered into a layout like this:

.. code-block:: html

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>{{page_title}}</title>
            <link href="/semantic-ui/css/semantic.min.css" rel="stylesheet" type="text/css" media="all" />
            <script src="/js/jquery.min.js"></script>
            <script src="/js/jquery.form.js"></script>
            <script src="/js/formXHR.js"></script>
            <script src="/js/formHelperSemantic.js"></script>
        </head>
        <body id="example">
            <div id="main container" style="width: 1200px; margin: auto">
                {{{contact}}}
            </div>
        </body>
    </html>

The system will then handle all the business and display logic of handling errors and saving the data in the database.


.. _pubsub:


PubSub
++++++

Fork on github here: `form source <https://github.com/virtuecenter/pubsub>`_

The PubSub pattern is a great way to separate components in a highly decoupled manner.  In the form above, there is no logic for saving data in the database.  All the form component does, is publish a "topic" with the data attached, and if there is a subscriber to the topic the data would get saved.  In theory, there could be multiple subscribers, one for sending and email, one for logging the data, andother for indexing it in the search engine -- any number of subscribers can listen to a single topic.

First, the subscriptions, in YAML format:

.. code-block:: yaml

	topics:
	    form-contact-save: 
	       PostToDB: ['post', 'db']

You can see in the example above, the topic is "form-contact-save".  

The PostToDB subscriber is specifying that it wants to receive the "post" and "db" services from the dependency injection container.

Also, all subscibers receive as their first argument, the "event" which is an array containing contextual information... such as the data to save.

Here is the subscriber that stores data in the database that is published when forms are submitted:

.. code-block:: php

	<?php
	return function ($event, $post, $db) {
		if (!isset($event['dbURI']) || empty($event['dbURI'])) {
			throw new \Exception('Event does not contain a dbURI');
		}
		if (!isset($event['formMarker'])) {
			throw new \Exception('Form marker not set in post');
		}
		$document = $post->{$event['formMarker']};
		if ($document === false || empty($document)) {
			throw new \Exception('Document not found in post');
		}
		$documentObject = $db->documentStage($event['dbURI'], $document);
		$documentObject->upsert();
		$post->statusSaved();
	};

----------

.. _separation:

Separation
++++++++++

Fork on github here: `separation source <https://github.com/virtuecenter/separation>`_

Separation is a way to bind the data returned from a RESTful API to a "partial" template that will render the data into a complete page "layout".

Opine-PHP uses separation under-the-hood for rendering collections and forms.  Here is a simple example for how it may be used for rendering a homepage:

.. code-block:: php

  $this->separation->layout('home')->template()->write();

In the above example, it will load the "public/layouts/home.html" layout file.

Then, it calls the template() function which is intended as a verb in this instance, like "template this layout".

And, then, it calls the "write()" method to output the merged logic.

Below is the YAML file for the contact form.  It would use the layout and template specified above to bring all the "separate" aspects together.

The config file is concerned with:

* the id of the section in the layout to populate markup into, in this example: contact, header and footer
* a RESTful API "url"
* "args" to pass to the data URL
* a "partial" template to render the data with
* a built in "type" for type-specific rendering logics

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
