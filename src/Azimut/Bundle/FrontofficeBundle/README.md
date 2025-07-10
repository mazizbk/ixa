Azimut Frontoffice Bundle
=========================

This bundle handle sites' structure.

Sites
\___ Menus
   \___ Pages
      \___ Zones
        \___ CmsFileAttachments (from CmsBundle)


Site entity has a relation to SiteLayout, wich define it's menus and visual template.

Page has a relation to PageLayout, defining it's visual template and options.
Available PageLayout options:

 - standalone_cmsfiles_routes: set wether cmsFiles inside the page have their own individual route or not (boolean)

PageLayout can set multiple zones, each accepting a specific list of cmsFile types (default is all)

Exemple of PageLayout creation:

    $layout = new PageLayout();
    $layout
        ->setName('My template name')
        ->setTemplate('my_template.html.twig')
        ->setOptions([
            'standalone_cmsfiles_routes' => true
        ])
        ->createZoneDefinition('left')
        ->getLayout()
        ->createZoneDefinition('center', [
            'accepted_attachment_classes' => [
                CmsFileArticle::class,
                CmsFileContact::class
            ]
        ])
        ->getLayout()
        ->createZoneDefinition('right')
        ->getLayout()
    ;



How to insert a custom component in a page ?
--------------------------------------------

Custom components go into FrontofficeCustomBundle. For example a contact form.

*1. create an action in a controller*

if request needed, inject it as originalRequest :

    public function myCustomAction(Request $originalRequest)

*2. create a template*

create a template inside FrontofficeCustomBundle view directory and render it inside your controller.

*3. insert your component in a fontoffice or cms layout*

Your can insert your component anywhere you want inside a twig template :

    {{ render(controller('AzimutFrontofficeCustomBundle:myCustom:myCustom', {'originalRequest': app.request})) }}
