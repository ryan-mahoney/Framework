Separation
==========

Separation is a software service for binding logic-less templates to JSON data sources.

Introduction
++++++++++++

These days, more websites are being built without direct database queries, but with RESTful API calls that return JSON. Additionally, whereas in the traditional MVC approach to building websites Views would have logic in them, Views are being done away with in favor of logic-less templates, or, the slightly more flexible less-logic templates.

The FMF framework relies heavily on Handlebars for rendering HTML and RESTful APIs for all data.  As such, the Separation library allows us to create a configuration file that specified where all the partials in an HTML layout will obtain their data from, and then it processes each partial outputting a fully populated HTML template.

Beyond its core features, Separation has the ability to to interact with a few specialized types of bindings, such as for working with FMF's custom data APIs, such as Collection, Document and Form. 