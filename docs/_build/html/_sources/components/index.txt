Components
==========

.. _collection:

Collection
++++++++++

Fork on github here: `collection source <https://github.com/virtuecenter/collection>`_

Collections are at the heart of how FMF makes data accessable.  FMF will create RESTful APIs that product JSON data, as well as front-end routes for collection data on a website front-end.

Collection files are typically small, but they may contain logic for transforming individual documents returned:

.. code-block:: php

	<?php
	use UrlId\UrlId;

	class videos {
		use Collection\Collection;
		public $publishable = true;
		public static $singular = 'video';

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

..code-block:: json

	{
	    "blogs": [
	        {
	            "_id": "50490cb9b79921001200000b",
	            "authors": [
	                "50491761b79921131200000a"
	            ],
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
	            "tags": [

	            ],
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
* byAuthorSlug
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

.. _separation:

Separation
++++++++++

.. _pubsub:

PubSub
++++++