Theming
=======

Applying a visual theme to a website or mobile-web application is a critical aspect of the success of the project.  It won't matter how scalable the site is if it's users are not provided with a high quality experience.

---------

Sample Project
++++++++++++++

Here is a link to a `Demo Project <https://github.com/virtuecenter/demo>`_. You can download, build and run this project and see a practical example of how an Opine-PHP works, and specifically, how it is themed.

 * download and build as you would any Opine-PHP project
 * create a database in Mongo called "opinedemo", with user "demo" and password as "password".  From MongoDB terminal: use opinedemo; db.addUser('demo', 'password');
 * import the data: "tar xzf dump.tar.gz", then "cd dump", mongorestore -d opinedemo ./opinedemo
 * configure your webserver, and edit your hosts file to point opine-demo.localhost to 127.0.0.1

---------

Listing Auto-Generated Routes
+++++++++++++++++++++++++++++

To see a list of the routes that are defined in your project, simply go to the "/routes" path of your project in a browsers and they will be listed.

For example, if you are working with the "blogs" *Collection*, the routes "/blogs" and "/blog/:slug.html" will automatically be routed in your project.  The same is true for forms that you use.

----------

Types of Pages
++++++++++++++

The first consideration when theming an Opine-PHP page is to know what *type* of page you are dealing with.  For example, the page could be for a *Collection*, a *Document*, a *Form* or it may be a custom page, such as a homepage.  Determining the type is important, because the type will tell you where to find it's constituent files.  

Every page is going to need and app.yml file, a layout.html file and at least one partial.hbs file.  

When you build you project, the builder will put an app under "/app/collections/blogs.yml", a layout under "/public/layouts/blogs.html", and a partial under "/public/partials/blogs.hbs".  In this sense, you start out with a "laced-up" application, and just need to modify these files.  For *Collections*, the builder will also create stub files for the related *Document* versions, so "/app/document/blog.yml", "/public/layout/blog.html" and "/public/partial/blog.hbs" will also be created.

At this point, you primary need to be focused on adding the relevant CSS, HTML structure and JS to your project and of course, being aware of the data structure supplied by your API so you can subsitute data in your partials file.

Listing Data APIs & Viewing JSON data
*************************************

*Collections* will each provide a data API URL.  To see the collections available for your project, go to the "/collections" path in a browser and they will be listed.  Clicking on a link will show you a pretty-printed representation of the data that makes knowing the field names you will need for partials easy.

---------

Working with Layouts
++++++++++++++++++++

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

The above code block is a typical layout file.  Often it will not contain too much markup because a most of the markup will be found in the partials for this layout.  HandlebarsPHP will be used to substitute the variables, that is why they are all in the triple curly-bracket format.  

In this example, there are 5 sections that will be substituted: {{{title}}}, {{{header}}}, {{{pages}}}, {{{twitter}}}, {{{footer}}}.  Each one of these names corresponds to a particular binding in the app files that specifies where the data will come from, and which partial to render it with.

---------

Working with Partials
+++++++++++++++++++++

.. code-block:: html

	<ul class="" style="padding:0; border:0; margin:0">
	{{#each menus}}
		<li><a href="{{url}}">{{label}}</a></li>
	{{/each}}
	</ul>

Partials are just the individual parts of a webapages HTML.  Opine-PHP currently has standardized on HandlearsPHP as a templating language.  In the example above, the API must be providing a array of data called "menus", and each menu has a "url" and "label" attribute.

If you need to know more about how Handlebars works, refer to the Javascript website as all the documentation is there and the PHP version functions the same way.

----------

Layout Applications
+++++++++++++++++++++++

One of the distinct features of Opine-PHP is that it is largely driven by configuration files.  Wherever possible, simple configuration files are used in place of mechanical coding.  The Layout library is used to read a YAML file, and then pull in the data for each binding, provide the data to the themeing engine, obtain the markup from a partial, and put that markup into a layout... and finally, return a fully populated layout file.

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

In the code block above, there are 3 bindings.  "contact" will obtain the markup for the individual form fields of a contact form and provide it to a partial template for rendering the form.  "header" and "footer" will call URLs that supply raw HTML and render them directly into the appropriate variables in the layout file.

---------

Best Practices
++++++++++++++

Headers and Footers
*******************

Page Titles
***********