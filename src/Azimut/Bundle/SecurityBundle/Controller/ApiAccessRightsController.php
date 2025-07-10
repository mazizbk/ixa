<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-22 14:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Form\Type\AccessRightType;
use Azimut\Bundle\SecurityBundle\Form\Type\GroupAccessRightType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Form\Type\UserAccessRightType;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 *  @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class ApiAccessRightsController extends FOSRestController
{
    /**
     * Get types action
     * @return array
     *
     * @ApiDoc(
     * section="Security",
     * description="Security : Get access right types"
     * )
     */
    public function getAccessrightsAvailabletypesAction()
    {
        $types = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->getAvailableTypes();

        return array(
            'types' => $types,
        );
    }

    /**
     * Get all action
     * @param int|null $userId
     * @param int|null $groupId
     * @param bool     $inheritedRights If set to yes, result will be combined rights of user and its groups
     * @return array
     * @internal param Request $request
     * @Rest\View(serializerGroups={"list_access_rights"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get all access rights by user id, group id or all of them"
     * )
     * @QueryParam(
     *  name="userId", nullable=true, requirements="\d+", description=" User Id"
     * )
     * @QueryParam(
     *  name="groupId", nullable=true, requirements="\d+", description=" Group Id"
     * )
     * @QueryParam(
     *  name="inheritedRights", nullable=true, requirements="\d", description="Get inherited rights (from groups)"
     * )
     */
    public function getAccessrightsAction($userId = null, $groupId = null, $inheritedRights = false)
    {
        $em = $this->getDoctrine()->getManager();
        $accessRights = [];
        $inheritedRightsArr = [];
        if ($userId != null && $groupId != null) {
            throw new \InvalidArgumentException("UserId and GroupId can't be set at same time");
        } elseif ($userId != null && $groupId == null) {
            $user = $em->getRepository(User::class)->find($userId);
            if ($user !== null) {
                if ($user->hasAccessRights() !== false) {
                    $accessRights = $user->getAccessRights()->toArray();
                }
                if ($inheritedRights) {
                    foreach ($user->getGroups() as $group) {
                        $inheritedRightsArr = array_merge($inheritedRightsArr, $group->getAccessRights()->toArray());
                    }
                }
            }
        } elseif ($userId == null && $groupId != null) {
            $group = $em->getRepository('AzimutSecurityBundle:Group')->find($groupId);
            if ($group !== null) {
                if ($group->hasAccessRights() !== false) {
                    $accessRights =  $group->getAccessRights();
                }
            }
        } else {
            $accessRights = $em->getRepository(AccessRight::class)->findAll();
        }

        $result = [
            'accessRights' => $accessRights,
        ];
        if ($inheritedRights) {
            $result['inheritedAccessRights'] = $inheritedRightsArr;
        }

        return $result;
    }


    /**
     * Get action
     * @var integer $id Id of the access right
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_access_right", "list_access_rights"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get an access right by id"
     * )
     */
    public function getAccessrightAction($id)
    {
        $accessRight = $this->getAccessRightEntity($id);
        $class = get_class($accessRight);
        return array(
            'accessRight' => $accessRight,
            'class' => $class,
        );
    }

    /**
     * Post action
     * @var Request $request
     * @throws \InvalidArgumentException
     * @return View|array
     *
     * @Rest\View(serializerGroups={"access_right"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Create a new access_right. Caution : access_right type is dynamic, see access_right type list for complete input capabilities",
     *  input="access_right",
     *  output="Azimut\Bundle\SecurityBundle\Entity\AccessRight"
     * )
     */
    public function postAccessrightsAction(Request $request)
    {
        if (!$request->request->get('access_right')) {
            throw new \InvalidArgumentException("Access Right not found in posted datas.");
        }

        if (empty($request->request->get('access_right')['type'])) {
            throw new \InvalidArgumentException("Access Right type has to be defined.");
        }

        $type = $request->request->get('access_right')['type'];
       // $objectId = '';
        $tempAr = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->createInstanceFromString($type)
        ;//here is where access right is created

        $accessRight = $this->uniqueAccessRight($request->request);

        if (empty($request->request->get('access_right')['user'])) {
            $form = $this->createForm(AccessRightType::class, $tempAr, array(
                'group' => true,
                'csrf_protection' => false
            ));
        }
        if (empty($request->request->get('access_right')['group'])) {
            $form = $this->createForm(AccessRightType::class, $tempAr, array(
                'user' => true,
                'csrf_protection' => false
            ));
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($accessRight != null) {
                if (count($accessRight->getRoles()) == 0) {
                    $this->deleteAccessrightAction($accessRight->getId());
                    return $this->view(null, Response::HTTP_NO_CONTENT);
                } else {
                    $accessRoles = $form->getData()->getRoles();
                    foreach ($accessRoles as $role) {
                        $accessRight->addRole($role);
                    }
                }
            } else {
                $accessRight = $tempAr;
                if (!empty($request->request->get('access_right')['accessRightType']['objectId'])) {
                    $object = $this->getDoctrine()
                            ->getRepository($accessRight->getObjectClass())
                            ->find($request->request->get('access_right')['accessRightType']['objectId']);
                    $accessRight->setObject($object);
                }
                if (count($accessRight->getRoles()) == 0) {
                    return $this->view(null, Response::HTTP_NO_CONTENT);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($accessRight);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_security_api_get_accessright',
                    array('id' => $accessRight->getId())
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
     * @throws \InvalidArgumentException
     * @return array|View
     *
     * @Rest\View(serializerGroups={"detail_access_right", "list_access_rights"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Update an access_right. Caution : access_right type is dynamic, see access_right type list for complete input capabilities",
     *  input="access_right",
     *  output="Azimut\Bundle\SecurityBundle\Entity\AccessRight"
     * )
     */
    public function putAccessrightsAction(Request $request, $id)
    {
        if (!$request->request->get('access_right')) {
            throw new \InvalidArgumentException("Access Right not found in posted datas.");
        }

        if (empty($request->request->get('access_right')['type'])) {
            throw new \InvalidArgumentException("Access Right type has to be defined.");
        }

        $accessRight = $this->getAccessRightEntity($id);
        $objectId = '';
        if (!empty($request->request->get('access_right')['accessRightType']['objectId'])) {
            $objectId = $request->request->get('access_right')['accessRightType']['objectId'];
        }

        if (empty($request->request->get('access_right')['user'])) {
            $form = $this->createForm(AccessRightType::class, $accessRight, array(
                    'group' => true,
                    'method' => 'PUT',
                    'csrf_protection' => false
                ));
        }
        if (empty($request->request->get('access_right')['group'])) {
            $form = $this->createForm(AccessRightType::class, $accessRight, array(
                    'user' => true,
                    'method' => 'PUT',
                    'csrf_protection' => false
                ));
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (count($accessRight->getRoles()) == 0) {
                $this->deleteAccessrightAction($accessRight->getId());
                return $this->view(null, Response::HTTP_NO_CONTENT);
            } else {
                if ($objectId && $accessRight->getObjectId() != $objectId) {
                    $object = $this->getDoctrine()
                                ->getRepository($accessRight->getObjectClass())
                                ->find($objectId);
                    $accessRight->setObject($object);
                    $em->persist($accessRight);
                    $em->flush();
                }
            }

            return array(
                'accessRight' => $accessRight
                );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @throws \InvalidArgumentException
     * @return View|array
     *
     * @Rest\View(serializerGroups={"detail_access_right", "list_access_rights"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Update user's access_rights. Caution : access_right type is dynamic, see access_right type list for complete input capabilities",
     *  input="user_access_right",
     *  output="Azimut\Bundle\SecurityBundle\Entity\User"
     * )
     */

    public function putUseraccessrightAction(Request $request, $id)
    {
        if (!$request->request->get('user_access_right')) {
            throw new \InvalidArgumentException("User Access Rights not found in posted datas.");
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AzimutSecurityBundle:User')->find($id);

        $form = $this->createForm(UserAccessRightType::class, $user, array(
            'csrf_protection' => false,
            'method' => 'PUT'
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$this->isGranted('SUPER_ADMIN') && $user->getId() == $this->getUser()->getId()) {
                // We know current user has APP_SECURITY access, we just need to check if it wasn't removed
                $hasAppSecurity = false;
                $accessRights = $user->getAccessRights();
                foreach ($accessRights as $accessRight) {
                    if ($accessRight->hasRole('APP_SECURITY')) {
                        $hasAppSecurity = true;
                        break;
                    }
                }
                if (!$hasAppSecurity) {
                    foreach ($user->getGroups() as $group) {
                        foreach ($group->getAccessRights() as $accessRight) {
                            if ($accessRight->hasRole('APP_SECURITY')) {
                                $hasAppSecurity = true;
                                break;
                            }
                        }
                    }
                }
                if (!$hasAppSecurity) {
                    throw $this->createAccessDeniedException($this->get('translator')->trans('security.edituser.rights.removing_group_would_remove_access'));
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return array(
                'user' => $user
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @throws \InvalidArgumentException
     * @return View|array
     *
     * @Rest\View(serializerGroups={"detail_access_right", "list_access_rights"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Update group's access_rights. Caution : access_right type is dynamic, see access_right type list for complete input capabilities",
     *  input="group_access_right",
     *  output="Azimut\Bundle\SecurityBundle\Entity\Group"
     * )
     */

    public function putGroupaccessrightAction(Request $request, $id)
    {
        if (!$request->request->get('group_access_right')) {
            throw new \InvalidArgumentException("Group Access Rights not found in posted datas.");
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('AzimutSecurityBundle:Group')->find($id);

        $form = $this->createForm(GroupAccessRightType::class, $group, array(
            'csrf_protection' => false,
            'method' => 'PUT'
        ))
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$this->isGranted('SUPER_ADMIN')) {
                // Prevent user from removing APP_SECURITY right from the last group that gives him it
                $hasAppSecurity = false;
                /** @var User $user */
                $user = $this->getUser();
                $accessRights = $user->getAccessRights();
                foreach ($accessRights as $accessRight) {
                    if ($accessRight->hasRole('APP_SECURITY')) {
                        $hasAppSecurity = true;
                        break;
                    }
                }
                if (!$hasAppSecurity) {
                    foreach ($user->getGroups() as $group) {
                        foreach ($group->getAccessRights() as $accessRight) {
                            if ($accessRight->hasRole('APP_SECURITY')) {
                                $hasAppSecurity = true;
                                break;
                            }
                        }
                    }
                }
                if (!$hasAppSecurity) {
                    throw $this->createAccessDeniedException($this->get('translator')->trans('security.editgroup.rights.removing_group_would_remove_access'));
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return array(
                'group' => $group
            );
        }

        return array(
            'form' => $form,
        );
    }


    /**
     * Delete action
     * @var integer $id Id of the access right
     * @return View
     * @ApiDoc(
     *     section="Security",
     *     description="Security : Delete an access right"
     * )
     */
    public function deleteAccessrightAction($id)
    {
        $accessRight = $this->getAccessRightEntity($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($accessRight);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get access_right entity instance
     * @var integer $id Id of the access_right
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return AccessRight
     */
    protected function getAccessRightEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $accessRight = $em->getRepository('AzimutSecurityBundle:AccessRight')->find($id);

        if (!$accessRight) {
            throw $this->createNotFoundException('Unable to find AccessRight '.$id);
        }

        return $accessRight;
    }

    /**
     * Getting the unique type access_right that connects a user or a group to a role, obj etc
     * @param $requestParams
     * @return mixed
     */
    private function uniqueAccessRight(ParameterBag $requestParams)
    {
        $type = $requestParams->get('access_right')['type']; // type ok checked

        if (empty($requestParams->get('access_right')['user'])) {
            //goes in here if group set to true
            $groupId = $requestParams->get('access_right')['group'];
            $aright = $this->groupUniqueAccessRight($groupId, $type, $requestParams);
        } else {
            //goes in here if user set to true
            $userId = $requestParams->get('access_right')['user'];
            $aright = $this->userUniqueAccessRight($userId, $type, $requestParams);
        }


        return $aright;
    }

    private function userUniqueAccessRight($userId, $type, $requestParams)
    {
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->findAccessRightsByUser($userId, $type, $requestParams);

        return $aright;
    }

    private function groupUniqueAccessRight($groupId, $type, $requestParams)
    {
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->findAccessRightsByGroup($groupId, $type, $requestParams);

        return $aright;
    }
}
