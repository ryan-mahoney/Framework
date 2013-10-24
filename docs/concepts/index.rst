Concepts
========

Build and Re-Build
++++++++++++++++++

Making a modern website or mobile-website is complicated, and as such, folder structures have become cluttered.  FMF provides a facility for building projects initially, which sets up all the folders and some configurations you will need.  Just run the following command:

.. code-block:: bash

   cd myproject
   php public/index.php build

Also, the FMF build process handles a variety of caches.  Whenever you add or change *config*, *collections*, *forms*, *filter* or *subscribers* files, you will need to re-run the build commond.  It may seem tedious, but it runs fast and it's a heck of a lot better to have a build process and a fast website than to have a website that is slow because it lacks optimization.  

In the future, the build process may also work with `grunt <http://gruntjs.com/>`_ to minify JS/CSS and possibly build the entire project as a `phar <http://php.net/manual/en/book.phar.php>`_ for easy distribution and deployment.

-------

Auto-Generated Routes
+++++++++++++++++++++

A lot of "lacing-up" of application routes is handled automatically by FMF.  For example, if you have a *form* file named "contact" in your project, the route /form/contact will automatically be created.  The same is true for *collections*.  So, if you have a *collection* "blogs", then the "/blogs" and "blog/:slug" routes will also be generated.

This is convenient, but it also helps if you are building many website with the same data collections, because you will get familiar with the route layout and it will make development and testing easier.

-------

API Centric
+++++++++++

Some frameworks really emphasize their amazing `object relational mappers <http://four.laravel.com/docs/eloquent>`_ [ORMs].  ORMs are tools that try to make the banal work of writing SQL commands more modern and automated.  ORMs are crap.  They make queries that are easy to write... easy to write... and complex queries even more difficult.  But -- why would you use a SQL database in the first place, right?

If you are using FMF, you are also using `MongoDB <http://www.mongodb.org/>`_ (or a derivive like `TokuMX <http://www.tokutek.com/products/tokumx-for-mongodb/>`_), so an ORM won't help you much, as the native `mongo <http://pecl.php.net/package/mongo>`_ driver provides a simple mechanism for writing queries.  

But then, why are you writing queries?  FMF comes with a very robust RESTful API for working with MongoDB data sources and discourages you as much as possible from writing queries.  Instead, you should be creating APIs and depending on built in components like the *form* component for saving, validating and sanitizing data for you.

At a time when mobile applications are so pervasive, you are going to have to write and API anyway -- so you might as well just create one universal API for both web and mobile applications and be done with it.

End rant.

-------

Service Oriented Architecture
+++++++++++++++++++++++++++++

I know `Service Oriented Architecture <http://en.wikipedia.org/wiki/Service-oriented_architecture>`_ [SOA] sounds like some kind of nonsense buzzword, but bear with me.  By utilizing a `dependency injection container <https://github.com/virtuecenter/container>`_, FMF provides a range of "services" to applications that creates an excellent decoupling of logic and maximizes the potential for unit-testing the framework and your own services.

This approach to architecting software has been popular in other languages like Java for a long time and it's finally catching on in PHP.  The reason is simple -- it makes applications easier to test and maintain.

-------

Less-Logic Templates
++++++++++++++++++++

Isn't PHP itself a templating language?  Well, that's how I always looked at it.  Smarty?  No thank you!  But then, as a plethora of JS based templating languages emerged, I realized that the real beauty of logic-less templates is that they really force the API to provide data in a useful format and prevent front-end developers from writing queries.

I think the battle for best templating language is not over yet, but for now, I choose Handlebars which is popular in `Javascript <http://handlebarsjs.com/>`_ and has a `PHP implementation <https://github.com/virtuecenter/handlebars.php>`_ as well.

-------

PubSub Pattern
++++++++++++++

This pattern allows parts of the application to publish a "topic" which other parts of your application can "subscribe" to.  This is another excellent strategy to acheive a high level of decoupling.  Making components more separate, more testable and more reusable.

-------

No-SQL
++++++

SQL is legacy.  I'm sorry.  It's been around for a long time, and I know it's not going away.  Neither is COBOL, but that doesn't mean we have to use it.  MongoDB, despite a few warts, seems to be the winner of No-SQL databases.  The main reason for using MongoDB for the persistent storage layer is that is speeds up development.  Once you are comfortable with it, it's much faster to develop applications using Mongo than say MySQL.

-------

Search
++++++

Just about every application needs to have a search facility.  Search is a `hard problem <http://en.wikipedia.org/wiki/NP-hard>`_ that thankfully has been more or less solved by `Lucene <http://lucene.apache.org/>`_.  `Solr <http://lucene.apache.org/solr/>`_ is a fairly simple application that handles both the indexing and retreival of data.  Solr is built into FMF in a number of ways and will make your search consumtion easier whenever you need search in your application... which is always. 

-------

Queues
++++++

Once your application needs to do some API calls to remote systems or do some data processing that takes a while to run (can you say Map-Reduce?), then you are probably going to need a queue and some workers running to get this stuff done in the background.  Without a queue, your application's users need to wait around on your application.  Nobody likes waiting.  Under the hood, the `Beanstalkd <http://kr.github.io/beanstalkd/>`_ queue server is used.  Each website gets it's own queue and worker that is managed transparently by the build script.
