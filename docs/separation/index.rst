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

**project/app/hompeage.yaml**

.. code-block:: yaml
    imports:
     - base.yml

    js:

    binding:
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

In the configation file above,  is a YAML file that Separation would read to know:

 * which other Separation files to import (header, footers, etc.)
 * any JS files it will compile
 * most importantly, which data to bind


 Data Binding
 ++++++++++++

 In the yaml file above, there would be a related HTML "layoutt" file, like the one below.

 