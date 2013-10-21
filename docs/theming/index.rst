Theming
=======

Applying a visual theme to a website or mobile-web application is a critical aspect of the success of the project.  It won't matter how scalable the site is if it's users are not provided with a high quality experience.

---------

Sample Project
++++++++++++++

Here is a link to a sample project. You can download, build and run this project and see a practical example of how an FMF works, and specifically, how it is themed.

---------

Listing Auto-Generated Routes
+++++++++++++++++++++++++++++

To see a list of the routes that are defined in your project, simply go to the "/routes" path of your project in a browsers and they will be listed.

For example, if you are working with the "blogs" *Collection*, the routes "/blogs" and "/blog/:slug.html" will automatically be routed in your project.  The same is true for forms that you use.

----------

Types of Pages
++++++++++++++

The first consideration when theming an FMF page is to know what *type* of page you are dealing with.  For example, the page could be for a *Collection*, a *Document*, a *Form* or it may be a custom page, such as a homepage.  Determining the type is important, because the type will tell you where to find it's constituent files.  

Every page is going to need and app.yml file, a layout.html file and at least one partial.hbs file.  

When you build you project, the builder will put an app under "/app/collections/blogs.yml", a layout under "/public/layouts/blogs.html", and a partial under "/public/partials/blogs.hbs".  In this sense, you start out with a "laced-up" application, and just need to modify these files.  For *Collections*, the builder will also create stub files for the related *Document* versions, so "/app/document/blog.yml", "/public/layout/blog.html" and "/public/partial/blog.hbs" will also be created.

At this point, you primary need to be focused on adding the relevant CSS, HTML structure and JS to your project and of course, being aware of the data structure supplied by your API so you can subsitute data in your partials file.

Listing Data APIs & Viewing JSON data
*************************************

*Collections* will each provide a data API URL.  To see the collections available for your project, go to the "/collections" path in a browser and they will be listed.  Clicking on a link will show you a pretty-printed representation of the data that makes knowing the field names you will need for partials easy.


---------

Handlebars PHP
++++++++++++++


---------

Separataion App
+++++++++++++++


---------

Working with Layouts
++++++++++++++++++++



Working with Partials
+++++++++++++++++++++

Headers and Footers
+++++++++++++++++++

Page Titles
+++++++++++