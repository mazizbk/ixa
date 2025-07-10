<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 10:01:12
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Form\Type\PageLayoutType;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
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
class ApiPageLayoutController extends FOSRestController
{
    /**
      * Get all action
      * @var Request $request
      * @return array
      *
      * @Rest\View(serializerGroups={"list_page_layouts"})
      *
      * @ApiDoc(
      *  section="Frontoffice",
      *  resource=true,
      *  description="Frontoffice : Get all page layouts"
      * )
      */
    public function getPagelayoutsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $pageLayouts = $em->getRepository(PageLayout::class)->findAll();

        return [
            'pageLayouts' => $this->get('azimut_security.filter')->serializeGroup($pageLayouts, ['list_page_layouts']),
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the page layout
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_page_layout"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get a page layout by id"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getPagelayoutAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $pageLayout = $this->getPageLayoutEntity($id);
        if (!$this->isGranted('VIEW', $pageLayout)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'pageLayout' => $pageLayout,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Create a new page layout",
     *  input="pageLayout",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\PageLayout"
     * )
     */
    public function postPagelayoutsAction(Request $request)
    {
        if (!$request->request->get('page_layout')) {
            throw new HttpException(400, "Page layout not found in posted datas.");
        }

        TranslationProxy::setDefaultLocale('all');

        $pageLayout = new PageLayout();

        $form = $this->createForm(PageLayoutType::class, $pageLayout, [
            'csrf_protection' => false
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($pageLayout);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_frontoffice_api_get_pagelayout',
                    ['id' => $pageLayout->getId()]
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
     * @var integer $id Id of the page layout
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_page_layout"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update page layout",
     *  input="pageLayout",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\PageLayout"
     * )
     */
    public function putPagelayoutAction(Request $request, $id)
    {
        if (!$request->request->get('page_layout')) {
            throw new HttpException(400, "Page layout not found in posted datas.");
        }

        TranslationProxy::setDefaultLocale('all');
        $pageLayout = $this->getPageLayoutEntity($id);

        $form = $this->createForm(PageLayoutType::class, $pageLayout, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'pageLayout' => $pageLayout,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Delete action
     * @var integer $id Id of the page layout
     * @return View
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Delete page layout"
     * )
     */
    public function deletePagelayoutAction($id)
    {
        $pageLayout = $this->getPageLayoutEntity($id);
        $em = $this->getDoctrine()->getManager();

        $pagesUsingLayoutCount = $em->getRepository(Page::class)->getPageContentsCountByLayout($pageLayout);

        if (0 < $pagesUsingLayoutCount) {
            throw new HttpException(400, sprintf("Can't delete page layout because it is used by %s page(s).", $pagesUsingLayoutCount));
        }

        $em->remove($pageLayout);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get page layout entity instance
    * @var integer $id Id of the page layout
    * @return Azimut\Bundle\FrontofficeBundle\Entity\PageLayout
    */
    protected function getPageLayoutEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $pageLayout = $em->getRepository(PageLayout::class)->find($id);

        if (!$pageLayout) {
            throw $this->createNotFoundException('Unable to find page layout '.$id);
        }

        return $pageLayout;
    }
}
