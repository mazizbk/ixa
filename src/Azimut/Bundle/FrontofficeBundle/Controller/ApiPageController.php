<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-07 15:08:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Form\Type\PageType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use JMS\Serializer\SerializationContext;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class ApiPageController extends FOSRestController
{
    /**
     * Get action
     * @return array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get available page types"
     * )
     */
    public function getPageAvailabletypesAction()
    {
        $types = $this->getDoctrine()
            ->getRepository(Page::class)
            ->getAvailableTypes();

        return array(
            'types' => $types,
        );
    }

    /**
     * Get all action
     * @return array
     *
     * @Rest\View(serializerGroups={"list_pages"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  resource=true,
     *  description="Frontoffice : Get all pages"
     * )
     */
    public function getPagesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $pages = $em->getRepository(Page::class)->findAll();

        return array(
            'pages' => $this->get('azimut_security.filter')->serializeGroup($pages, ['list_pages']),
        );
    }

    /**
     * Get action
     * @param int  $id
     * @param null $locale
     * @return array
     * @internal param int $id Id of the page
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get a page by id"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getPageAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $page = $this->getPageEntity($id);

        if (!$this->isGranted('VIEW', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        // Here we do the doctrine object to array convertion "by hand" because
        // we wan't to use a different serialize group for children pages

        // use detail_page context for the page we are fetching
        $serializer = $this->container->get('jms_serializer');

        $pageArray = json_decode($serializer->serialize($page, 'json', SerializationContext::create()->setGroups(array('detail_page'))), true);

        // switch to list_pages context for fetching the children pages
        $em = $this->getDoctrine()->getManager();
        $childrenPages = $em->getRepository(Page::class)->findBy(array('parentPage' => $pageArray['id']));
        $childrenPagesArray = $this->get('azimut_security.filter')->serializeGroup($childrenPages, ['list_pages']);

        // inject the children pages
        $pageArray['childrenPages'] = $childrenPagesArray;

        return array(
            'page' => $pageArray,
            'pageEditIsGranted' => ($this->isGranted('EDIT_PARAMS', $page) && !$page->isPageParametersLocked()) || $this->isGranted('SUPER_ADMIN'),
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Create a new page",
     *  input="page",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Page"
     * )
     */
    public function postPagesAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('all');
        $em = $this->getDoctrine()->getManager();

        if (!$request->request->get('page')) {
            throw new \InvalidArgumentException("Page not found in posted datas.");
        }

        if (empty($request->request->get('page')['type'])) {
            throw new \InvalidArgumentException("Page type has to be defined.");
        }

        $type = $request->request->get('page')['type'];
        /** @var Page $page */
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->createInstanceFromString($type)
        ;

        $form = $this->createForm(PageType::class, $page, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            if (!$this->isGranted('EDIT_PARAMS', $page)) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
            }

            // If parent menu is locked, do not authorize page creation (except for superadmin)
            if (null != $page->getMenu() && $page->getMenu()->isFirstPageLevelLocked() && !$this->isGranted('SUPER_ADMIN')) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('first.level.pages.are.locked.in.this.menu'));
            }

            // If parent menu max elements if reached, do not authorize page creation
            if (null != $page->getMenu() && $page->getMenu()->isMaxPagesCountReached()) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('this.menu.cannot.contain.more.than.%count%.pages', ['%count%' => $page->getMenu()->getMaxPagesCount()]));
            }

            $em->persist($page);

            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_frontoffice_api_get_page',
                    array('id' => $page->getId())
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
     * @var integer $id Id of the Page
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_page"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update page",
     *  input="page",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Page"
     * )
     */
    public function putPageAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $page = $this->getPageEntity($id);

        if (!$this->isGranted('EDIT_PARAMS', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        // This should have been done on voter (implies transforming EDIT_PARAMS permission handling)
        if ($page->isPageParametersLocked() && !$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(PageType::class, $page, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        return $this->updatePage($request, $page, $form);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the page
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_page"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update page",
     *  input="page",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Page"
     * )
     */
    public function patchPageAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $page = $this->getPageEntity($id);

        if (!$this->isGranted('EDIT_PARAMS', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(PageType::class, $page, array(
            'method' => 'PATCH',
            'csrf_protection' => false
        ));

        return $this->updatePage($request, $page, $form);
    }

    /**
     * Delete action
     * @var integer $id Id of the page
     * @return View
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Delete page"
     * )
     */
    public function deletePageAction($id)
    {
        $page = $this->getPageEntity($id);

        if (!$this->isGranted('EDIT_PARAMS', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        // If parent menu is locked, do not authorize page creation (except for superadmin)
        if (null != $page->getMenu() && $page->getMenu()->isFirstPageLevelLocked() && !$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('first.level.pages.are.locked.in.this.menu'));
        }

        $em = $this->getDoctrine()->getManager();

        $menu = $page->getMenu();
        $parentPage = $page->getParentPage();

        // reindex display orders in parent
        if (null != $menu) {
            $em->getRepository(Page::class)->decreaseMenuChildrenDisplayOrdersStartingAt($menu, $page->getDisplayOrder());
        } else {
            $em->getRepository(Page::class)->decreasePageChildrenDisplayOrdersStartingAt($parentPage, $page->getDisplayOrder());
        }

        $em->remove($page);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get page entity instance
    * @var integer $id Id of the page
    * @return \Azimut\Bundle\FrontofficeBundle\Entity\Page
    */
    protected function getPageEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository(Page::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException('Unable to find page '.$id);
        }

        return $page;
    }

    protected function updatePage($request, $page, FormInterface $form)
    {
        $em = $this->getDoctrine()->getManager();
        $currentPageMenu = $page->getMenu();

        $form->handleRequest($request);


        if ($page->getMenu() != $currentPageMenu) {
            // If old or new parent menu is locked, do not authorize page update (except for superadmin)
            if (!$this->isGranted('SUPER_ADMIN') && ((null != $page->getMenu() && $page->getMenu()->isFirstPageLevelLocked()) || (null != $currentPageMenu && $currentPageMenu->isFirstPageLevelLocked()))) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('first.level.pages.are.locked.in.this.menu'));
            }

            // If new parent menu max elements if reached, do not authorize page update
            if (null != $page->getMenu() && $page->getMenu()->isMaxPagesCountReached()) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('this.menu.cannot.contain.more.than.%count%.pages', ['%count%' => $page->getMenu()->getMaxPagesCount()]));
            }
        }

        if ($form->isValid()) {
            $em->flush();

            return [
                'page' => $page,
            ];
        }

        return [
            'form' => $form,
        ];
    }
}
