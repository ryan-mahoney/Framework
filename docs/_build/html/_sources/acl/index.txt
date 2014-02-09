Access Control
==============

Access Control is a universal concern for website development.  How is access enforced, applied, revoked, etc.  

Opine-PHP makes access control fairly manageable through a range of APIs and consistant user interfaces.


The Basics
++++++++++

Users belong to groups, this is managed via the "Users" manager.

Groups have access to routes.

Routes are specified in Yaml files.

To grant a user specific access to a route, the user is placed in a group.  

Then specify how you want your access applied to routes by adding or modifying one of the files in your project's /acl folder.


Configuring Access Control
++++++++++++++++++++++++++

The "imports" section is incase you want to reference another ACL.yaml file.

The "groups" block specified a sub-section for each group, with a list of which routes or regexes are restricted to the group.

You can specify a specific path to a route, or a regex if you want to restrict access to a family of pages.

The redirectLogin, redirectDenied do what the seem like.  If the user is not logged in, they will be redirected to the login.  If the user is logged in, but does not have access, they are redirected to another route.

.. code-block:: yaml

  imports:

  groups:
      member:
          routes:
              - '/member/dashboard'
              - '/form/directory'
              - '/form/profile'
              - '/jobs'
          regexes:
              - '/^job/[a-z0-9\-_]*.html$/'
          redirectLogin: '/form/login'
          redirectDenied: '/page/denied.html'

      registered:
          routes:
              - '/form/membership'
          redirectLogin: '/form/login'
          redirectDenied: '/page/denied.html'
