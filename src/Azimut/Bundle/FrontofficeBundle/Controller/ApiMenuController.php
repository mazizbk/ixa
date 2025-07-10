<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-07 15:08:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Form\Type\MenuType;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class ApiMenuController extends FOSRestController
{

    /**
     * Get all action
     * @return array
     *
     * @Rest\View(serializerGroups={"list_menus"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  resource=true,
     *  description="Frontoffice : Get all menus"
     * )
     */
    public function getMenusAction()
    {
        $em = $this->getDoctrine()->getManager();

        $menus = $em->getRepository(Menu::class)->findAll();

        return array(
            'menus' => $this->get('azimut_security.filter')->serializeGroup($menus, ['list_menus']),
        );
    }

    /**
     * Get action
     * @var integer $id Id of the menu
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_menu"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get a Menu by id"
     * )
     */
    public function getMenuAction($id)
    {
        $menu = $this->getMenuEntity($id);

        return array(
            'menu' => $this->get('azimut_security.filter')->serializeGroup($menu, ['detail_menu']),
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the Menu
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_menu"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update menu",
     *  input="menu",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Menu"
     * )
     */
    public function putMenuAction(Request $request, $id)
    {
        $menu = $this->getMenuEntity($id);
        if (!$this->isGranted('SUPER_ADMIN')) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('security.only_super_admin_can_create_menus'));
        }

        $form = $this->createForm(MenuType::class, $menu, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return array(
                'menu' => $menu,
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
    * Private : get menu entity instance
    * @var integer $id Id of the menu
    * @return Menu
    */
    protected function getMenuEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $menu = $em->getRepository(Menu::class)->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('Unable to find menu '.$id);
        }

        return $menu;
    }
}
