<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-02-03 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Form\Type\GroupType;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use FOS\RestBundle\View\View;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class ApiGroupController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_group"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Get all groups"
     * )
     */
    public function getGroupsAction(Request $request)
    {
        $gm = $this->container->get('azimut.group_manager');
        $groups = $gm->findGroups();

        return array(
            'groups' => $groups,
        );
    }

    /**
     * Get action
     * @var integer $id Id of the group
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_group"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get a Group by id"
     * )
     */
    public function getGroupAction($id)
    {
        $group = $this->getGroupEntity($id);

        return array(
            'group' => $group,
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Create a new group",
     *  input="Azimut\Bundle\SecurityBundle\Form\Type\GroupType",
     *  output="Azimut\Bundle\SecurityBundle\Entity\Group"
     * )
     */
    public function postGroupsAction(Request $request)
    {
        $gm = $this->container->get('azimut.group_manager');
        $group = $gm->createGroup();
        $form = $this->createForm(GroupType::class, $group, array('csrf_protection' => false));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $group = $form->getData();
                $gm->updateGroup($group);

                return $this->redirectView(
                    $this->generateUrl(
                        'azimut_security_api_get_group',
                        array('id' => $group->getId())
                        )
                    );
            }
        }

        return array(
        'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the Group
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_group"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Update group",
     *  input="Azimut\Bundle\SecurityBundle\Form\Type\GroupType",
     *  output="Azimut\Bundle\SecurityBundle\Entity\Group"
     * )
     */
    public function putGroupAction(Request $request, $id)
    {
        $group = $this->getGroupEntity($id);
        $gm = $this->container->get('azimut.group_manager');
        $form = $this->createForm(GroupType::class, $group, array(
            'csrf_protection' => false,
            'method' => 'PUT'
        ));

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
    public function deleteGroupAction($id)
    {
        $groupManager = $this->container->get('azimut.group_manager');
        $group = $groupManager->findGroupBy(array('id' => $id));

        if (!$this->isGranted('SUPER_ADMIN')) {
            // Prevent user from deleting the last group that gives him access to APP_SECURITY
            $hasAppSecurity = false;
            /** @var User $user */
            $user = $this->getUser();
            foreach ($user->getAccessRights() as $accessRight) {
                if ($accessRight->hasRole('APP_SECURITY')) {
                    $hasAppSecurity = true;
                    break;
                }
            }
            if (!$hasAppSecurity) {
                foreach ($user->getGroups() as $userGroup) {
                    if ($userGroup->getId() == $group->getId()) {
                        continue;
                    }
                    foreach ($userGroup->getAccessRights() as $accessRight) {
                        if ($accessRight->hasRole('APP_SECURITY')) {
                            $hasAppSecurity = true;
                            break;
                        }
                    }
                }
            }
            if (!$hasAppSecurity) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('security.deletegroup.deleting_group_would_remove_access'));
            }
        }

        $groupManager->deleteGroup($group);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get group entity instance
    * @var integer $id Id of the group
    * @return Azimut\Bundle\SecurityBundle\Entity\Group
    */
    protected function getGroupEntity($id)
    {
        $gm = $this->container->get('azimut.group_manager');
        $group = $gm->findGroupBy(array('id' => $id));

        if (!$group) {
            throw $this->createNotFoundException('Unable to find Group '.$id);
        }

        return $group;
    }
}
