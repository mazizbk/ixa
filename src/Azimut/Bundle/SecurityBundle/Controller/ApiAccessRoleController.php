<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-06-03 08:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 *  @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class ApiAccessRoleController extends FOSRestController
{
    /**
     * Get all action
     * @var boolean showAppClasses
     * @return array
     *
     * @Rest\View(serializerGroups={"role"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Get all roles"
     * )
     * @QueryParam(
     *  name="showAppClasses", nullable=true
     * )
     */
    public function getRolesAction($showAppClasses = null)
    {
        $accessRightService = $this->get('azimut_security.access_right_service');

        if (null === $showAppClasses) {
            $em = $this->getDoctrine()->getManager();
            $accessRoles = $em->getRepository('AzimutSecurityBundle:AccessRole')->findAll();
        } else {
            if ($showAppClasses == 'false') {
                $accessRoles = $accessRightService->getApplicationAccessRoles();
            } else {
                $accessRoles = $accessRightService->getAllAccessRolesByApplication(false);
            }

            // Translate access roles names
            foreach ($accessRoles as $key => &$accessRole) {
                if ($accessRole instanceof AccessRole) {
                    $appNameTranslationKey = strtolower(substr($accessRole->getRole(), 9, strlen($accessRole->getRole())));
                    $appName = $this->get('translator')->trans($appNameTranslationKey.'.app.name');
                    $accessRole->setName($appName);
                } elseif (array_key_exists('name', $accessRole)) {
                    $accessRole['name'] = $this->get('translator')->trans($accessRole['name'].'.app.name');
                }
            }
        }

        return array(
            'roles' => $accessRoles,
        );
    }

    /**
     * Get action
     * @var integer $id Id of the role
     * @return array
     *
     * @Rest\View(serializerGroups={"role"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get a Role by id"
     * )
     */
    public function getRoleAction($id)
    {
        $accessRole = $this->getRoleEntity($id);

        return array(
            'role' => $accessRole,
        );
    }

    /**
     * Get action
     * @var string $class Class on which these roles can be applied
     * (with "_" instead of "/" ex. Azimut_Bundle_FrontofficeBundle_Entity_Menu)
     * @return array
     *
     * @Rest\View(serializerGroups={"role"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get a Role by Class on which is applied"
     * )
     */
    public function getRolesonclassAction($class)
    {
        $accessRoleProvider = $this->get('azimut_security.role_provider_chain');
        $services = $accessRoleProvider->getProviders();
        $accessRoles = array();
        $em = $this->getDoctrine()->getManager();
            //$accessRoles = $em->getRepository('AzimutSecurityBundle:AccessRole')->findAll();

        $class = str_replace("_", "\\", $class);
        $roleIds = [];

        foreach ($services as $service) {
            if ($service->getEntities() != null) {
                $accessRolesOnEntities = $service->getRolesOnEntities();

                if (isset($accessRolesOnEntities[$class])) {
                    $accessRoles = $accessRolesOnEntities[$class];
                }
            }
        }
        foreach ($accessRoles as $role) {
            $roleIds[] = $em->getRepository('AzimutSecurityBundle:AccessRole')->findOneBy(
                                array('role' => $role)
                            )->getId();
        }

        return array(
            'roles' => $roleIds,
        );
    }

    /**
     * Get action
     * @return array
     *
     * @Rest\View(serializerGroups={"role"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get a Roles and Entities/Classes on which is applied"
     * )
     */
    public function getRolesonentitiesAction() //name not in camel case coz of routes in rest if camel case route would be /roles/on/classes
    {
        $accessRoleProvider = $this->get('azimut_security.role_provider_chain');
        $services = $accessRoleProvider->getProviders();
        $rolesOnEntities = array();

        foreach ($services as $service) {
            if ($service->getRolesOnEntities() != null) {
                $rolesOnEntities[] = $service->getRolesOnEntities();  //split roles on entities according to application: $rolesOnEntities[$service->getName()]
            }
        }

        return array(
            'rolesOnEntities' =>  $rolesOnEntities,
        );
    }


    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the Group
     * @return array
     *
     * @Rest\View(serializerGroups={"group"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Update group",
     *  input="Azimut\Bundle\SecurityBundle\Form\Type\GroupType",
     *  output="Azimut\Bundle\SecurityBundle\Entity\Group"
     * )
     */
    public function putRoleAction(Request $request, $id)
    {
        $group = $this->getGroupEntity($id);
        $gm = $this->get('azimut.group_manager');
        $form = $this->createForm(new GroupType($gm->getClass()), $group, array('csrf_protection' => false));

        if ($request->getMethod() == 'PUT') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $group = $form->getData();
                $gm->updateGroup($group);

                return array(
                    'group' => $group,
                );
            }
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the Group
     * @return View
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Delete group"
     * )
     */
    public function deleteRoleAction($id)
    {
        $groupManager = $this->get('azimut.group_manager');
        $group = $groupManager->findGroupBy(array('id' => $id));
        $groupManager->deleteGroup($group);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get role entity instance
     * @var integer $id Id of the role
     * @return \Azimut\Bundle\SecurityBundle\Entity\AccessRole
     */
    protected function getRoleEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $accessRole = $em->getRepository('AzimutSecurityBundle:AccessRole')->find($id);

        if (!$accessRole) {
            throw $this->createNotFoundException('Unable to find role '.$id);
        }

        return $accessRole;
    }
}
