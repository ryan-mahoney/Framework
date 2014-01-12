Access Control
==============

Access Control is a universal concern for website developers.  How is access enforced, applied, revoked, etc.  FMF makes access control fairly manageable through a range of APIs and consistant user interfaces.


The Basics
++++++++++

Users belong to groups.

Groups have access to zones.

Zones consist of routes.

To grant a user specific access to a route, the user is placed in a group.  Then, either via the project's acl.yml file or direct API, the routes of a zone are defined.  In the database, user's are assigned to groups and groups are mapped to zones.

