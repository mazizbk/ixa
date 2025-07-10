Azimut Mediacenter Bundle
=========================

This bundle handle media files and folder management.


How to use ?
------------

This bundle plugs itself into AzimutBackOfficeBundle AngularJS app (see EventListener/ConfigureBackofficeAppsListener.php)

All the files manipulations relies on an API, located at /api/mediacenter. API involves FOSRestBundle with automated routes. Routes configuration are defined into Resources/config/routing_api.yml.

API documentation can be found by browsing /api/doc in dev environnement.



Installation
------------

Add the following route entry to app/config/routing.yml:

    AzimutMediacenterBundleApi:
        type: rest
        resource: "@AzimutMediacenterBundle/Resources/config/routing_api.yml"
        prefix: /api/mediacenter


Creating a new media type
-------------------------

To create a new type of media, simply use the generator command:

    app/console mediacenter:generate:mediatype

This will create all the required entities, form types and services. Then you just have to edit the entities to add your desired fields.

