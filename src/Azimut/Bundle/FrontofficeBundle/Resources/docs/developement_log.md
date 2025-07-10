FrontofficeBundle
==================

Helpful to understand the way the service of access rights and voters is implemented.

http://symfony.com/doc/current/cookbook/service_container/compiler_passes.html
http://symfony.com/fr/doc/current/components/dependency_injection/compilation.html
http://symfony.com/fr/doc/current/components/dependency_injection/tags.html


In page entity: page_parent useful for routing loader, parent which can be a menu or a website used for security in the voter method. 
In page repository we can find the meaning of the state of a page: published or not, hidden etc etc
