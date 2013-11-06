Data API
========

The philosophy of FMF is that the front-end developer should not need to write queries.  All of the queries should be pre-written or generated and be accessible via an HTTP API.

FMF utilized a software services named Collection to automatically provide a rich API in JSON format.

A Collection File
+++++++++++++++++

A complete list of available collections can be obtained `here <https://github.com/virtuecenter/collection/tree/master/available>`_.

To add a new collection to your project, simply copy the file to your own project's **/collections** folder.

A collection file looks like this:

.. code-block:: php

	<?php
	/*
	 * @version .1
	 * @link https://raw.github.com/virtuecenter/collection/master/available/blogs.php
	 * @mode upgrade
	 */
	namespace Collection;

	class blogs {
		public $publishable = true;
		public $singular = 'blog';
	}


**Note:**

 * whenever you add a new collection, don't forget to rebuild your project: php public/index.php build
 * to upgrade all of your collections to their latest versions, run: php public/index.php upgrade


Automatic API
+++++++++++++

The following HTTP API will be available for your collections:

**\http://project.com/json-data/collection/method/limit/offset/sort**

If the method takes and argument, it should be delimited with a hyphen, for example:

**\http://project.com/json-data/collection/method-arg/limit/offset/sort**

Complete List of Methods
++++++++++++++++++++++++

all
***

`Get all the documents in a collection. <>`_

byId
****

Get one document by its _id value.

bySlug
******

Get one document by it's code_name or "slug" value.

featured
********

Get documents marked as featured. 

byCategoryId
************

Get documents for a particular category, by the category id.

byCategory
**********

Get documents for a particular category, by the category name.

byCategoryFeatured
******************

Get documents marked as featured withing a categoory, by the category name.

byCategoryIdFeatured
********************

Get documents marked as featured withing a categoory, by the category id.

byTag
*****

Get documents matching a particular tag.

byTagFeatured
*************

Get documents marked as featured matching a particular tag.

byDateUpcoming
**************

Get documents with a date field that is upcoming.

byDatePast
**********

Get documents with a date field set in the past.

byAuthorId
**********

Get documents set to a particular author, by the author id.

byAuthor
********

Get documents set to a particular author, by the author name.