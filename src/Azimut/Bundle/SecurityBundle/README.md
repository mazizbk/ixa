SecurityBundle
===============

A bundle implementing the security integration

Access Rights
---------------
A new way of adding access rights has been implemented. A table in the database
will be added. Relations ( User->AccessRight<-Object ).

 ___________                    _____________                    __________
|           |                  |             |                  |           |
|User/Group | -------------->  | AccessRight | ---------------> |AccessRole |
|___________|    OneToMany     |_____________|   ManyToMany     |___________|


Important
------
1. Static definition for roles and entities. See AzimutFrontofficeBundle/Security/AccessRoleService for example of a complete service defintion

2. The service "azimut_security.role_provider_chain" gathers all the services tagged as "role_provider"
   used to define all possible ROLES on all possible entities. Entities handled must implement
   Azimut/SecurityBundle/Entities/ObjectAwareInterface so they implement methods used for security.

3. Each bundle must create a service tagged with "role_provider" and a parameter alias with the bundle name
     syntax for the tag
     tags:
        - { name: role_provider, alias: security }

4. Service for access rights "azimut_security.access_right_service" implementing methods on add/remove access right.
    All cases of AccessRight are implemented: if object == null      ==> AcccessRightRole
                                              if object == string    ==> AccessRightClass
                                              if object instance of ObjectAwareInterface ==> AccessRightObject

    ex. of syntax : $service = $container->get('azimut_security.access_right_service');
                    $service->addAccessRight($user, $role, $object );
                    $service->removeAccessRight($user, $role, $object);


got rid of prefixes in roles to simplify voters access.indented
in the view template used with a prefix for translation facilities

Best Practice
For protecting broad URL patterns, use access_control;
Whenever possible, use the @Security annotation;
Check security directly on the security.authorization_checker service whenever you have a more complex situation.

For fine-grained restrictions, define a custom security voter;
For restricting access to any object by any user via an admin interface, use the Symfony ACL.