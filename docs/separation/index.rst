Separation
==========

Separation is a software service for binding logic-less templates to JSON data sources.

Introduction
++++++++++++

These days, more websites are being built without direct database queries, but with RESTful API calls that return JSON data. 

Traditionally, the MVC approach to building websites would have Views containing presentation logic.  Many modern applications are getting rid of Views in favor of logic-less templates, or, the slightly more flexible less-logic templates.

The FMF framework relies heavily on the Handlebars engine for rendering HTML and RESTful APIs for all data.  As such, the Separation library allows us to create a configuration file that specified where all the partials in an HTML layout will obtain their data from, and then it processes each partial outputting a fully populated HTML template.

Beyond its core features, Separation has the ability to to interact with a few specialized types of bindings, such as for working with FMF's custom data APIs, such as Collection, Document and Form.

A Sample Configuration File
+++++++++++++++++++++++++++

Configuration file: **project/app/hompeage.yaml**

.. code-block:: yaml

	imports:
	 - base.yml

	js:

	binding:
	    blogs:
            url: '%dataAPI%/json-data/blogs/all/10/0/{"display_date":-1}'
            args: []
            partial: 'collections/blogs.hbs'
            type: 'Collection'
	    about:
	        url: '%dataAPI%/json-data/blurbs/all'
	        args: []
	        partial: '{{{blurbs.about}}}'
	        type: 'Collection'
	    contactbrief:
	        url: '%dataAPI%/json-form/contactbrief'
	        args: []
	        partial: 'forms/contactbrief.hbs'
	        type: "Form"


In the configuration file above,  is a YAML file that Separation would read to know:

 * which other Separation files to import (header, footers, etc.)
 * any JS files it will compile
 * most importantly, which data to bind


HTML Layout Template (Outer Template)
+++++++++++++++++++++++++++++++++++++

In the yaml file above, there would be a related HTML layout file, like the one below.

The import thing to notice, is that the layout file container Handlebar variables, one for each binding.  So, the "blogs" binding in the YAML configuration file, also has a {{{blogs}}} variable in the HTML layout file.

It's also important to note, that by default, homepage.yml will be associated to homepage.html.

HTML Layout file: **project/public/layout/homepage.html**

.. code-block:: html

	<html>
		<body>
			<div class="container">
				<div class="left">
					<div class="blog-posts">{{{blogs}}}</div>
				</div>
				<div class="right">
					<div class="about-me">{{{about}}}</div>
					<div class="contact-form">{{{contactbrief}}}</div>
				</div>
			</div>
		</body>
	</html>


Data Binding, JSON API data and Less-Logic Partial Templates
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

In the example above, there is binding called "blogs", see below:

.. code-block:: yaml

	blogs:
        url: '%dataAPI%/json-data/blogs/all/10/0/{"display_date":-1}'
        args: []
        partial: 'collections/blogs.hbs'
        type: 'Collection'

For this binding, Separation will send and HTTP GET request to the url: 

%dataAPI%/json-data/blogs/all/10/0/{"display_date":-1}

%dataAPI% is a variable set in the projects database config that usually specified the URL of the project, but could specify an external data-source.  This is helpful for abstracting out the URL so it doesn't need to be changed when switching from development to production.  It is the same as:

http://project.localhost/json-data/blogs/all/10/0/{"display_date":-1}

This URL will return some JSON, that probably looks like:

.. code-block:: json

	{
	    "blogs": [
	        {
	            "_id": "50490cb9b79921001200000b",
	            "body": "Body",
	            "code_name": "human_readable",
	            "comments": "t",
	            "description": "Description",
	            "display_date": {
	                "sec": 1346126400,
	                "usec": 0
	            },
	            "featured": "f",
	            "image": {
	                "name": "us-davis-pepper-spray.jpg",
	                "size": "411507",
	                "type": "image\/jpeg",
	                "url": "http:\/\/virtuecenter.s3.amazonaws.com\/files\/2012-09-06-16\/us-davis-pepper-spray.jpg",
	                "height": "453",
	                "width": "680"
	            },
	            "pinned": "f",
	            "status": "published",
	            "tags": [],
	            "title": "Title",
	            "display_date__MdY": "Aug 28, 2012",
	            "path": "/blog/human_readable.html"
	        }
	    ]
	}


Then, Separation will take that data, and render it with the less-logic partials file: **projct/public/collections/blogs.hbs**

.. code-block:: html

	{{#each}}
		<div><a href="{{path}}">{{title}}</a></div>
	{{/each}}


Special Types
+++++++++++++

Separation has some special internal logic for dealing with certain data API types, such as Collection, Form and Document API types.  The bottom line, is that these types return JSON data, but sometimes there are some particular ways of calling them.


Tips and Tricks
+++++++++++++++

This section show a few short-cuts and work arounds for using Separation.

Inline Partials
***************

It is possible not to specify an partial file, but to put the Handlebar logic directly into the configuration file.  This may sound silly, but if you are just substituting a single value from an data source, it may be more efficient.  For example:


.. code-block:: yaml

	about:
	    url: '%dataAPI%/json-data/blurbsReportByTag/all'
	    args: []
	    partial: '{{{blurbs.about}}}'
	    type: 'Collection'

The above example will pull a list of "blurbs" and then the handlebar logic will render the "about" key of the blurbs response JSON.


Fetching HTML
*************

In some cases, you don't want to use a logicless template, you want to either plug in static HTML from a file, or have a script generate the HTML the old fashioned way.  No proble, refer to the URL of the HTML, and specify the type as "html".

.. code-block:: yaml
    
    header:
        url: '%dataAPI%/Manager/header'
        type: 'html'
