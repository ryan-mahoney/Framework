Concepts
========

Build and Re-Build
++++++++++++++++++

Making a modern website or mobile-website can be complicated, and as such, folder structures have become more and more cluttered.  FMF provides a facility for building projects initially, which is to basically go into your projects root folder, and run the following command:

.. code-block:: bash

   cd myproject
   php public/index.php build

Also, FMF build a variety of caches that the build process also handles.  So, whenever you add or changes config, collections, forms, filter or subscribers, you will also need to run that commond.  It may seem tedious, but it runs fast and it's a heck of a lot better to have a build process and a fast website than no build process and a slow website.  

In the future, the build process may also work with grunt to minify JS/CSS and possibly build the project as a phar for easy distribution and deployment.

-------

Auto-Generated Routes
+++++++++++++++++++++

A lot of "lacing-up" of application routes is handled automatically by FMF.  So, for example, if you have a *form* file named "contact" in your project, the route /form/contact will automatically be created.  The same is true for collections.  So, if you have a *collection* "blogs", then the "/blogs" and "blog/:slug" routes will be generated.

-------

API Centric
+++++++++++

Some framework really emphasize their object-relational-mappers [ORMs].  ORMs are tools that try to make the banal work of working with antiquated SQL commands more object oriented.  Basically, ORMs are crap.  They make queries that are easy to write... easy to write and complex queries even more difficult.  But -- why would you use a SQL database in the first place, right?

If you are using FMF, you are also using `MongoDB <http://www.mongodb.org/>`_ (or a derivive like `TokuMX <http://www.tokutek.com/products/tokumx-for-mongodb/>`_) then and ORM won't help you, the native driver provides a simple mechanism for writing queries.  But then, why are you writing queries?  FMF comes with a very robust RESTful API for working with MongoDB data sources and discourages you as much as possible from writing queries.  Instead, you should be creating APIs and depending on built it components like the *form* component for writing inserts for you.

At a time where mobile applications are so pervasive, if your application is important, you're going to have to write and API anyway -- so you might as well just create one universal API for both web and mobile applications.

End rant.

-------

Service Oriented Architecture
+++++++++++++++++++++++++++++

I know Service Oriented Architecture [SOA] sounds like some kind of BS buzzword, but bear with me.  My utilizing a dependency injection container, FMF provides a range of "services" to applications that creates an excellent decoupling of logic and maximizes the potential for unit-testing the framework and your own services.

This approach to architecting software has been popular in other languages like Java for a long time and it's finally catching on in PHP.  The reason is simple -- it's an excellent way of achieveing decoupling.

-------

Less-Logic Templates
++++++++++++++++++++

Isn't PHP itself a templating language?  Well, that's how I always looked at it.  Smarty?  No thank you!  But then, as a plethora of JS bases templating languages emerged, I realized that the real beauty of logic-less templates is that they really force the API to provide data in a useful format and prevent front-end developers from writing queries.

I think the battle for best templating language is not over yet, but for now, I choose Handlebars which is popular in Javascript and has a PHP implementation as well.

-------

PubSub Pattern
++++++++++++++

This pattern allows parts of the application to publish a "topic" which other parts of your application can "subscribe" to.  This is another excellent strategy to acheive a high level of decoupling.

-------

No-SQL
++++++

SQL is legacy.  I'm sorry.  It's been around for a long time, it's not going away.  Neither is COBOL, but that doesn't mean we have to still use it.  MongoDB, despite a few warts, seems to be the winner in this area.  The main reason for using this for the persistent storage layer is that is speeds up development.  Once you are comfortable with it, it's much faster to develop applications using Mongo than say MySQL.

-------

Search
++++++

Just about every application need to have a search facility.  Search is a hard problem that thankfully has been more or less solved by Lucene.  Solr is a fairly simple application that handles both the indexing and retreival of data.  Solr is built into FMF in a number of ways and will make your life much easier whenever you need search in your application... which is always. 

-------

Queues
++++++

Once your application needs to do some API calls to remote systems or do some data processing that takes a while to run (can you say Map-Reduce?), then your probably going to need a queue and some workers running to get this stuff done.  Without a queue, your application users need to wait around on your application.  Nobody likes waiting.  Under the hood, the Beanstalkd queue server is used.  Each website gets it's own queue and worker that is managed transparently by the build process. 
