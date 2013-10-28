Bundles
=======

Bundles allow for packages applications to be embedded within other FMF applications.  Bundles should not be confused with services.  If you create a new service, you would simply add it to the composer.json and the projects container and use the new service objects. Bundles should be used when you are distributing an application that has it's own routes and visual layouts.

Quick notes about bundles (complete documentation coming soon)

* Bundles can have their own "forms" but not their own collections, collections always belong to parent projects
* Bundles always have a base folder the routed URL that matches the bundle name, for example: /Somebundle/foo or just /Somebundle
* Bundles have their own public assets, ie, css, layouts, js, etc -- probably not a good idea to reference the parents assets
* Bundles can have their own containers, these containers need to be added as includes in the project container
* Bundles are namespaced with their bundle name
* Bundles start with on file named Appliaction.php that receives the project container via constructor injection
* Bundles can access the $container->slim object to do their own routing 