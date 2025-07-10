# Upgrade from v1.4.0 to vX.X.X

## Update TinyMCE inclusion

Auto inclusion of TinyMCE in HTML head has been removed, it is now required to add this line in templates using TinyMCE :

```html
    {{ encore_entry_script_tags('tinymce/tinymce', null, 'backoffice') }}  
```

## Rewrite mediacenter file sorting

Ordering of Folder's content has been rewrote to an external service, to be reusable elsewhere. The local storage identifiers has changed, causing all stored sorting to be reverted to defaults.

## Store folder size on DB

Folder size is now pre-calculated and stored on DB each time a media declination, a media, or a folder is updated.

For this to work on existing DB, you will have to properly set the size property on each folder item.

## Add relation from MediaDeclinationAttachment to attached object (ex: CmsFile)

This inverse relation has been added to allow mediacenter to list all objects linked to a media. For existing databases, you have to set this relation for each existing CmsFileMediaDeclinationAttachment (the current unique implementation of MediaDeclinationAttachment), this has to reflect mainAttachment, secondaryAttachments, complementaryAttachment1, complementaryAttachment2, complementaryAttachment3, and complementaryAttachment4.

## Move hasStandaloneCmsfilesRoutes

The property hasStandaloneCmsfilesRoutes has been moved from page layout to zone definition.
Property standaloneRouterHasStandaloneCmsfilesRoutes has been added to page layout.

## Add site option to activate or not the search engine

The flag will be set to null when upgraging, wich leads to deactivate the search engine.

## Add required field on sites

Added property publisherName on Site, wich is used in site's and article's microdata.

## Update file proxy image resize sizes

Sizes and names has been modified to clarify the use of cropped methods.

## Update DB encoding

Database encoding has been switched from utf8 to utf8mb4

## Moved frontoffice security submit buttons from controller to template

All submit buttons have been removed from LoginController, and added to corresponding templates.

## Deletion of getQueryBuilderInstancesOfHavingValidPublicationDate in CmsFileRepository

Use getQueryBuilderPublishedByZoneId instead, passing zone id. 
This change has been necessery to allow entering zone definition to know the join stategy between cmsfile and translation.

## Updates in twig extension

The Twig function getMentionsLegalesLink has been removed, use getPagePathByLayout instead.

Example :

```html
<a href="getPagePathByLayout('demo/legal_notice.html.twig') }}">{{ 'legal.notice'|trans }}</a>
```

Deletion of getAbsolutePagePathByLayoutAndSiteLayout, use getPagePathByLayout with new options instead :

```html
<a href="getPagePathByLayout('demo/demo.html.twig', mySite) }}">...</a>
```

## Canonical URL improvement

The Twig function cmsFileCanonicalPath return the URL calculated by the router instead of full slug.

Demo templates extend SiteLayout/base.html.twig, wich factorize system dependent elements (ex: page canonical path, no index meta, ...).

## Remove deprecated media property

The "legend" field has been removed (was replaced by "caption")

## Update contact form

Default contact form template has been moved to "app/Resources/views/Forms/FrontofficeCustom/simple_form.html.twig"

And email template to : "app/Resources/views/Emails/FrontofficeCustom/simple_form_email.txt.twig"

## Update CmsFile repository

CmsFile repository can be fetched directly on its subclasses to increase Doctrine performances (in zone filters for instance).

CmsFileRepository must now be attached to each CmsFile subclass.

To upgrade, add this line on each of your custom CmsFile subclasses :

```php
<?php
/**
 *@ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 */
?>
```

# Upgrade from v1.3.0 to v1.4.0

## Update TranslatableEntityInterface

TranslatableEntityInterface has a new static method getTranslationClass.
All classes implementing it must declare this method.

Example to reproduce standard schema :
```
static function getTranslationClass()
{
    return static::class.'Translation';
}
```

NB : classes expending CmsFile don't need any change has it is defined in the main class.

This method allow a class to use a different translation class than its name postfixed with "Translation". You can even share the same translation class for several classes.

## Update Mediacenter widget html tag

Tabbed cmsfiles introduced new substates of the cmsfile edit view, we had to name the subview of the mediacenter widget to avoid collisions.

Replace :
```html
<az-mediacenter-widget><div class="main-panel" ui-view></div></az-mediacenter-widget>
```

By :
```html
<az-mediacenter-widget><div class="main-panel" ui-view="mediacenter-widget"></div></az-mediacenter-widget>
```

## AngularJS route parameter renamed in CMS

From now on the edit view of CMS files has tabs (to handles comments for example).
To avoid subroutes parameters name mismatch, the "id" parameter of the route "backoffice.cms.file_detail" has been renamed to "file_id".

Bundles extending CMS must update their routes calling "CmsFileDetailController" to reflect this modification (and eventually their extended controllers).

Tip : search occurences of "angular.extend(this, $controller('CmsFileDetailController'".

Remember to update all links pointing to these routes (in list views for example).

CMS file's tabs are substates, in each application using or extending "CmsFileDetailController", we have to attach its substates by calling this in XxxApp.js :

```js
CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.myBundleName.file_detail');
```

## Changing in CmsFileSecondaryAttachmentsTrait

We now have to explicitly call trait constructor in CmsFile classes wich use it.

Ex :
```php
<?php
class CmsFileXxxxx extends CmsFile
{
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
    }
}
```

## New front home page route

To be able to remove the trailing slash of the url locale prefix for home page, a new route has been added : azimut_frontoffice_home

To avoid unecessary redirections, use this route instead of azimut_frontoffice with empty path to create home page link.

## New dynamic robot.txt

The robot.txt file has been removed from web folder.
It is now generated dynamically to be able to set the absolute URL of the sitemap depending on the site loaded.

Caution : the robot.txt physical is prioritary, so it must be removed when upgraging from older version.

Customisations have to be placed inside the following controller : FrontofficeBundle:Seo:robots.

## New media thumb cache parameter

These parameters has been added to configure file proxy thumb's cache :

```yml
parameters:
    media_thumb_cache_max_age: 180
    media_thumb_cache_shared_max_age: 180
```

## Front content submission has been added, it change the way "zone definitions" works.

After DB update, execute this SQL command:

```sql
UPDATE frontoffice_zone_definition SET type='cmsfiles';
```

In all Twig templates, replace:

```html
render(controller('AzimutFrontofficeBundle:Front:pageZone'
```

With:

```html
render(controller('AzimutFrontofficeBundle:Front:pageZoneCmsFiles'
```

A root folder has been added to store submitted file with cmsfile buffer entities.
Run this SQL query to add the folder :

```sql
INSERT INTO `mediacenter_folder` (`parent_folder_id`, `trashed_parent_folder_id`, `name`) VALUES
(NULL, NULL, 'Submitted.library');
```

## The template hierarchy has change

In site templates, replace :

```
    {% include pageLayout %}
```

With

```
    {% block body %}{% endblock %}
```


In page templates, extends siteLayout and include all in body block :

```
{% extends siteLayout %}

{% block body %}
    ...
{% endblock %}
```

## Deprecations

The "legend" property on mediaImage has been deprecated in favor of "caption" property.

The "copyright" property on mediaImageDeclination has been deprecated, use the "copyright" property of mediaImage instead (like is was already defined for mediaVideo).

# Upgrade from v1.3.0 to v1.3.1

Relation from PageLink to Page has changed, to not loose linkage you must run this query:

```sql
UPDATE frontoffice_page SET target_page_id = page_content_id;
```

# Upgrade from v1.2.0 to v1.3.0

Frontoffice secured pages have been added.
For each page in database apply :
 
 ```sql
 update frontoffice_page set userRoles = 'a:0:{}';
 ```

# Upgrade from v1.1.0 to v1.2.0

Support for CmsFile canonical path has been added.
In each CmsFile summary template, replace :

```html
{% if pagePath is defined %}
    <a href="{{ pagePath }}{{ cmsFile.slug }}">{{ 'read.more'|trans }}</a>
{% endif %}
```

with :

```html
{% if cmsFile.canonicalPath is not null %}
    <a href="{{ cmsFile.canonicalPath }}">{{ 'read.more'|trans }}</a>
{% elseif pagePath is not null %}
    <a href="{{ pagePath }}{{ cmsFile.slug }}">{{ 'read.more'|trans }}</a>
{% endif %}
```

# Upgrade from v1.0.0 to v1.0.1

To support the new search engine, a direct relation from Page to Site has been added.

Before merging, execute this query:

    ALTER TABLE frontoffice_page ADD site_id INT NOT NULL;

Then set the value of "site_id" for each page, depending on their parents.

Now you can safely merge new version.

# Upgrade from v1.0.0-RC7 to v1.0.0-RC8

## Definition of access right voters

Access right voters changed, RoleProviderChain is now injected to each voter service.
Debug StopWatch is no longer needed by these services.

In your service definition, remove :

    - "@?debug.stopwatch"

And add :
    
    - "@azimut_security.role_provider_chain"

For example, the definition of mediacenter voter service :

Before :

    services:
        azimut_mediacenter.access_right_voter:
            class: "%azimut_voter_class%"
            arguments:
                - "@service_container"
                - "@doctrine"
                - "@azimut_mediacenter.roles"
                - "@?debug.stopwatch"
                - "@?logger"
            tags:
                - { name: security.voter }
                - { name: monolog.logger, channel: security }

After :

    services:
        azimut_mediacenter.access_right_voter:
            class: "%azimut_voter_class%"
            arguments:
                - "@service_container"
                - "@doctrine"
                - "@azimut_mediacenter.roles"
                - "@azimut_security.role_provider_chain"
                - "@?logger"
            tags:
                - { name: security.voter }
                - { name: monolog.logger, channel: security }

# Upgrade from v1.0.0-RC6 to v1.0.0-RC7

## CmsFileFactory initialisation method

The initType function of CmsFileFactory has been removed.

The init function has been modified, by default it now inits all types no matter in wich namespace they are.

It has an optionnal parameter to restrict the initialized types to a particular namespace, based on the bundle base shortname.

For example, to init types for AzimutCmsContactBundle :
  
Before:

    CmsFileFactory.initType('contact');

After:

    CmsFileFactory.init('CmsContact');

