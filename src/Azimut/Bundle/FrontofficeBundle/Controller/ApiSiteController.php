<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-07 15:08:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Azimut\Bundle\FrontofficeBundle\Form\Type\SiteType;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class ApiSiteController extends FOSRestController
{
    /**
      * Get all action
      * @return array
      *
      * @Rest\View(serializerGroups={"list_sites"})
      *
      * @ApiDoc(
      *  section="Frontoffice",
      *  resource=true,
      *  description="Frontoffice : Get all sites"
      * )
      */
    public function getSitesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $sites = $em->getRepository(Site::class)->findAll();

        return array(
            'sites' => $this->get('azimut_security.filter')->serializeGroup($sites, ['list_sites']),
        );
    }

    /**
     * Get action
     * @param int  $id
     * @param null $locale
     * @return array
     * @internal param int $id Id of the site
     * @Rest\View(serializerGroups={"detail_site"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get a Site by Id"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getSiteAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $site = $this->getSiteEntity($id);
        if (!$this->isGranted('VIEW', $site)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return array(
            'site' => $site,
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Create a new site",
     *  input="site",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Site"
     * )
     */
    public function postSitesAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('all');
        $site = new Site();
        if (!$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('security.only_super_admin_can_create_sites'));
        }
        //annotation ????

        $form = $this->createForm(SiteType::class, $site, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($site);
            $em->flush();

            // auto create home page
            if ($site->getMenus()->count() > 0) {
                $page = new PageContent();

                $pageLayout = $em
                    ->getRepository('AzimutFrontofficeBundle:PageLayout')
                    ->findOneByName('simple')
                ;

                if (null == $pageLayout) {
                    throw new HttpException(400, "Can't create site's home page, no page layout named 'simple' found");
                }

                $page
                    ->setLayout($pageLayout)
                    ->setAutoSlug(false)
                    ->setMenu($site->getMenus()[0])
                ;

                foreach ($this->container->getParameter('locales') as $locale) {
                    $page
                        ->setMenuTitle('Home', $locale)
                        ->setSlug('', $locale)
                    ;
                }
            }

            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_frontoffice_api_get_site',
                    array('id' => $site->getId())
                    )
                );
        }

        return array(
        'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the site
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_site"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update site",
     *  input="site",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Site"
     * )
     */
    public function putSiteAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $site = $this->getSiteEntity($id);
        if (!$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('security.only_super_admin_can_edit_sites'));
        }

        $form = $this->createForm(SiteType::class, $site, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // force doctrine to reload from DB
            // (because of some collections not being reindexed)
            $em->clear();
            $site = $this->getSiteEntity($id);

            return array(
                'site' => $site,
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the site
     * @return View
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Delete site"
     * )
     */
    public function deleteSiteAction($id)
    {
        $site = $this->getSiteEntity($id);
        if (!$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('security.only_super_admin_can_delete_sites'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($site);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get site entity instance
    * @var integer $id Id of the site
    * @return Site
    */
    protected function getSiteEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $site = $em->getRepository(Site::class)->find($id);

        if (!$site) {
            throw $this->createNotFoundException('Unable to find site '.$id);
        }

        return $site;
    }
}
