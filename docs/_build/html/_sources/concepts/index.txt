Concepts
========

Build and Re-Build
++++++++++++++++++

Making a modern website or mobile-website can be complicated, and as such, folder structures have become more and more cluttered.  FMF provides a facilty for builing projects initially, which is to basically go into your projects root folder, and run the following command:

.. code-block:: bash

   cd myproject
   php public/index.php build

-------

Also, FMF build a variety of caches that the build process also handles.  So, whenever you add or changes config, collections, forms, filter or subscribers, you will also need to run that commond.  It may seem tedious, but it runs fast and it's a heck of a lot better to have a build process and a fast website than no build process and a slow website.  

In the future, the build process may also work with grunt to minify JS/CSS and possibly build the project as a phar for easy distribution and deployment.


Auto-Generated Routes
+++++++++++++++++++++

A lot of "lacing-up" of application routes is handled automatically by FMF.  So, for example, if you have a *form* file named "contact" in your project, the route /form/contact will automatically be created.  The same is true for collections.  So, if you have a *collection* "blogs", then the "/blogs" and "blog/:slug" routes will be generated.


API Centric
+++++++++++

Some framework really emphasize their object-relational-mappers [ORMs].  ORMs are tools that try to make the banal work of working with antiquated SQL commands more object oriented.  Basically, ORMs are crap.  They make queries that are easy to write... easy to write and complex queries even more difficult.  But -- why would you use a SQL database in the first place, right?

If you are using FMF, you are also using `MongoDB <http://www.mongodb.org/>`_ (or a derivive like `TokuMX <http://www.tokutek.com/products/tokumx-for-mongodb/>`_) then and ORM won't help you, the native driver provides a simple mechanism for writing queries.  But then, why are you writing queries?  FMF comes with a very robust RESTful API for working with MongoDB data sources and discourages you as much as possible from writing queries.  Instead, you should be creating APIs and depending on built it components like the *form* component for writing inserts for you.

At a time where mobile apps are so pervasive, if your application is important, you're going to have to write and API anyway -- so you might as well just create one universal API for both web and mobile applications.

End rant.

Service Oriented
++++++++++++++++

I know Service Oriented Architecture [SOA] sounds like some kind of BS buzzword, but bear with me.  My utilizing a dependecy injection container, FMF provides a range of "services" to applications that creates an excellent de-coupling of logic and maximizes the potential for unit-testing the framework and your own services.

Less-Logic Templates
++++++++++++++++++++

Isn't PHP itself a templating language?  Well, that's how I always looked at it.  Smarty?  No thank you!  But then, as a plethora of JS bases templating languages emerged, I realized that the real beauty of logic-less templates is that they really force the API to provide data in a useful format and prevent front-end developers from writing queries.

I think the battle for best templating language is not over yet, but for now, I choose Handlebars which is popular in Javascript and has a PHP implemenation as well.

PubSub Decoupling
+++++++++++++++++

No-SQL
++++++

Search
++++++

Queues
++++++