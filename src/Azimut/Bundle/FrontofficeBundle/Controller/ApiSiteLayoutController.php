<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 09:45:08
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Form\Type\SiteLayoutType;
use Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('SUPER_ADMIN')")
 */
class ApiSiteLayoutController extends FOSRestController
{
    /**
      * Get all action
      * @var Request $request
      * @return array
      *
      * @Rest\View(serializerGroups={"list_site_layouts"})
      *
      * @ApiDoc(
      *  section="Frontoffice",
      *  resource=true,
      *  description="Frontoffice : Get all site layouts"
      * )
      */
    public function getSitelayoutsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $siteLayouts = $em->getRepository(SiteLayout::class)->findAll();

        return [
            'siteLayouts' => $this->get('azimut_security.filter')->serializeGroup($siteLayouts, ['list_site_layouts']),
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the site layout
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_site_layout"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get a site layout by id"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getSitelayoutAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $siteLayout = $this->getSiteLayoutEntity($id);
        if (!$this->isGranted('VIEW', $siteLayout)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'siteLayout' => $siteLayout,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Create a new site layout",
     *  input="siteLayout",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout"
     * )
     */
    public function postSitelayoutsAction(Request $request)
    {
        if (!$request->request->get('site_layout')) {
            throw new HttpException(400, "Site layout not found in posted datas.");
        }

        TranslationProxy::setDefaultLocale('all');

        $siteLayout = new SiteLayout();

        $form = $this->createForm(SiteLayoutType::class, $siteLayout, [
            'csrf_protection' => false
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($siteLayout);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_frontoffice_api_get_sitelayout',
                    ['id' => $siteLayout->getId()]
                )
            );
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the site layout
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_site_layout"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update site layout",
     *  input="siteLayout",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout"
     * )
     */
    public function putSitelayoutAction(Request $request, $id)
    {
        if (!$request->request->get('site_layout')) {
            throw new HttpException(400, "Site layout not found in posted datas.");
        }

        TranslationProxy::setDefaultLocale('all');
        $siteLayout = $this->getSiteLayoutEntity($id);

        $form = $this->createForm(SiteLayoutType::class, $siteLayout, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'siteLayout' => $siteLayout,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Delete action
     * @var integer $id Id of the site layout
     * @return View
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Delete site layout"
     * )
     */
    public function deleteSitelayoutAction($id)
    {
        $siteLayout = $this->getSiteLayoutEntity($id);
        $em = $this->getDoctrine()->getManager();

        $sitesUsingLayoutCount = $em->getRepository(Site::class)->getSitesCountByLayout($siteLayout);

        if (0 < $sitesUsingLayoutCount) {
            throw new HttpException(400, sprintf("Can't delete site layout because it is used by %s site(s).", $sitesUsingLayoutCount));
        }

        $em->remove($siteLayout);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get site layout entity instance
    * @var integer $id Id of the site layout
    * @return Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout
    */
    protected function getSiteLayoutEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $siteLayout = $em->getRepository(SiteLayout::class)->find($id);

        if (!$siteLayout) {
            throw $this->createNotFoundException('Unable to find site layout '.$id);
        }

        return $siteLayout;
    }
}
