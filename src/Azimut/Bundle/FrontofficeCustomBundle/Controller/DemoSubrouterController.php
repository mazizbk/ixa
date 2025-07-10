<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-27 09:39:21
 */

namespace Azimut\Bundle\FrontofficeCustomBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact;

class DemoSubrouterController extends Controller
{
    public function indexAction($path, Site $site, Page $page, $pagePath, Request $request)
    {
        // $path contains the url fragment that is behind the url of the page, it is the inner
        // path of the subrouter. It may be used to fetch data, ex:
        //     $mySubrouterData = $mySubrouterObject->findSomethingBypath($path)

        $cmsFileContacts = $this->getDoctrine()->getRepository(CmsFile::class)->findByTypeHavingValidPublicationDates(CmsFileContact::getCmsFileType());

        $response = $this->render('PageLayout/'.$page->getTemplate(), array(
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageLayoutOptions' => $page->getTemplateOptions(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'page' => $page,
            'site' => $site,
            'path' => $path,
            'cmsFileContacts' => $cmsFileContacts,
            //'mySubrouterData' => $mySubrouterData
        ));

        $response->setMaxAge($this->container->getParameter('front_cache_max_age'));
        $response->setSharedMaxAge($this->container->getParameter('front_cache_shared_max_age'));

        return $response;
    }
}
