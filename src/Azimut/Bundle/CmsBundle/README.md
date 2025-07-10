Azimut CMS Bundle
=================

This bundle handle content files (like articles).


How to use ?
------------

This bundle plugs itself into AzimutBackOfficeBundle AngularJS app.

All the files manipulations relies on an API, located at /api/cms. API involves FOSRestBundle with automated routes. Routes configuration are defined into Resources/config/routing_api.yml.

API documentation can be found by browsing /api/doc in dev environnement.


Creating a new CMS file type
----------------------------

To create a new type of CMS file, simply use the generator command:

    bin/console cms:generate:cmsfiletype

This will create all the required entities, form types and services. Then you just have to edit the entities to add your desired fields.

Don't forget to update database schema :
    
    bin/console doctrine:schema:update
