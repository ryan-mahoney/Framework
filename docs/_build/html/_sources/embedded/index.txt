Embedded Managers
=================

When a project utilizes a document oriented data store, such as MongoDB, the functionality of nesting documents within documents is commonly used.  

For example, if you have a manager for “categories” it may have an embedded document that stores “sub-categories”.

To achieve this functionality, FMF and Semantic-CM have a rich set if logic and user interfaces for facilitating this use case.  That being said, it is at times counter-intuitive, so this document gives concrete examples


Steps to Embedding Manager
++++++++++++++++++++++++++

You will need two managers, one functioning as a “parent” and one as a “child”.

There are a few things to be mindful of:

- PC: the name of the parent storage collection, for example: “categories”
- HF: the name of the field in the parent that will hold embedded documents, for example: “subcategories”
- EM: the name of the embedded manager, for example: “subcategories”


Link the child from the parent
******************************

code sample of field php

code sample of field html


Designate the child as embedded
*******************************

public $embedded = true;

