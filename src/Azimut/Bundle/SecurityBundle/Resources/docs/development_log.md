SecurityBundle
===============

Explains shortly what has been done and what is left to do for implementing security in an app.

V.0
-------
Difficulties in annotation with entities: associations between user and different access rights. MappedSuperClass not useful for bidirectional OneToMany association, and DiscriminatorMap makes querys to heavy.

refs:
http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html

http://symfony.com/doc/current/cookbook/doctrine/event_listeners_subscribers.html

http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/strategy-cookbook-introduction.html


Note that the postLoad event occurs for an entity before any associations have been initialized. Therefore it is not safe to access associations in a postLoad callback or event handler.


1. Integration of FOSUserBundle
	 username == email address,  double entrance in the database but necessary otherwise Fos userbundle doesn't work.
	 added group entity and implementation of user <=> group associations

2. AccessRightInterface, an interface for every type of user access right, mandatory to implement.

3. GroupAccessRightInterface, an interface for every type of group access right, mandatory to implement.

3. AccessRightObjectAware, an interface that every type of object that needs access rights must implement.

4. SecurityVoter, voter created for global rights.

5. Each bundle needs to define it s user and group rights as well as their own voter(s). Tests done with AzimutFrontofficeBundle for integration with SecurityBundle.


V.1
-------
Using abstract class AccessRight to implement things in common between different types of rights.
Added DoctrineExtraBundle to load types of discriminator map dynamically.
Added AccessRole entity to represent rights on an object.

Implemented methods from object to access user throught accessrights, inverse not yet implemented.

V.2
----------
Added AccessRightAcl.
Extending form type to be able to add "is_granted"
Declaring formExtensionType in a service: By using the tag form.type_extension it will automatically be added to the Form component. Alias must be the same of the name of the extended field.


V.3
---------------
FOSUserBundle removed from application.
client_token parameter added for user registration verification
oauth_server_url parameter added for registration user request to server oauth
TO EDIT: for the moment url is oauthserver.dev needs to be changed in smth like localhost/azimut-oauth-server/web/


02/12/2014

Azimut\Bundle\SecurityBundle\EventListener\AuthenticationListener
Quand un AccessDeniedException est lancée le listener vérifie si c’est l’api qui lance cette exception on envoi juste un message d’erreur,sinon envoi vers le route de login(serveur ouate dans notre cas) avec ou sans la locale. L’url qui lance l’exception est stocke en session aussi.


Azimut\Bundle\SecurityBundle\Controller\BackofficeController
routeRedirectionAction besoin de finir mais avant verifier serveur OAuth. Et dans routing.yml changer le nom de la route si ne convient pas.