<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-02-03 14:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\AzimutLoginBundle\Model\User as ALUser;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Form\Type\UserType;
use FOS\RestBundle\Request\ParamFetcher;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpFoundation\Response;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class ApiUserController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_user"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Get all users"
     * )
     */
    public function getUsersAction()
    {
        $um = $this->container->get('azimut.user_manager');

        $superAdminUsers = [];
        if ($this->isGranted('SUPER_ADMIN')) {
            $superAdminUsers = $um->findSuperAdminUsers();
        }

        $users = $um->findUsers();

        return array(
            'users' => $users,
            'super_admin_users' => $superAdminUsers
        );
    }

    /**
     * Get action
     * @var integer $id Id of the user
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_user"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get a User by id"
     * )
     */
    public function getUserAction($id)
    {
        $user = $this->getUserEntity($id);

        return array(
            'user' => $user,
        );
    }

    /**
     * Post action
     * @var Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @return View|array
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Create a new user",
     *  input="Azimut\Bundle\SecurityBundle\Form\Type\UserType",
     *  output="Azimut\Bundle\SecurityBundle\Entity\User"
     * )
     */

    public function postUsersAction(Request $request)
    {
        $um = $this->container->get('azimut.user_manager');
        /** @var User $user */
        $user = $um->createUser();
        $form = $this->createForm(UserType::class, $user, array(
            'csrf_protection' => false,
            'isnew' => true,
            'method' => 'POST'
        ));

        if ($form->handleRequest($request)->isValid()) {
            //$user = $form->getData();

            $azimutLogin = $this->get('azimut_login_oauth_client');
            $email = $user->getUsername();
            $userOnLogin = $azimutLogin->getUserByEmailAddress($email);
            if (false === $userOnLogin) {
                // Automagically adds access
                $userOnLogin = $azimutLogin->createUserEmail($email, $request->getLocale());
            } else {
                // User exists, we need to allow access
                $azimutLogin->authorizeUser($userOnLogin);
            }
            $user
                ->setOauthId($userOnLogin->getId())
                ->setFirstName($userOnLogin->getFirstName())
                ->setLastName($userOnLogin->getLastName())
            ;

            if ($userOnLogin->isSuperAdmin) {
                $roleSuperAdmin = $this->getDoctrine()
                    ->getRepository('AzimutSecurityBundle:AccessRole')
                    ->findOneBy(['role' => 'SUPER_ADMIN'])
                ;

                $arRoles = new AccessRightRoles();
                $arRoles
                    ->addRole($roleSuperAdmin)
                    ->setUser($user)
                ;
                $user->addAccessRight($arRoles);
            }

            $um->updateUser($user);

            return $this->redirectView(
                $this->generateUrl(
                'azimut_security_api_get_user',
                    array('id' => $user->getId())
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
     * @var integer $id Id of the User
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_user"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Update user",
     *  input="Azimut\Bundle\SecurityBundle\Form\Type\UserType",
     *  output="Azimut\Bundle\SecurityBundle\Entity\User"
     * )
     */

    public function putUserAction(Request $request, $id)
    {
        $user = $this->getUserEntity($id);
        $um = $this->container->get('azimut.user_manager');

        $form = $this->createForm(UserType::class, $user, array(
            'csrf_protection' => false,
            'method' => 'PUT'
        ));
        $form->setData($user);

        if ($form->handleRequest($request)->isValid()) {
            if (!$this->isGranted('SUPER_ADMIN') && $user->getId() == $this->getUser()->getId()) {
                // Prevent user from removing itself from the last group that gives him access to APP_SECURITY
                $hasAppSecurity = false;
                foreach ($user->getAccessRights() as $accessRight) {
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
                    throw $this->createAccessDeniedException($this->get('translator')->trans('security.edituser.groups.removing_group_would_remove_access'));
                }
            }
            $um->updateUser($user);

            return array(
                'user' => $user
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the user
     * @return View
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Delete user"
     * )
     */
    public function deleteUserAction($id)
    {
        $userManager = $this->container->get('azimut.user_manager');

        if ($this->get('security.token_storage')->getToken()->getUser()->getId() == $id) {
            throw $this->createAccessDeniedException("Not allowed to delete your own user account");
        }

        /** @var User $user */
        $user = $userManager->findUserBy(array('id' => $id));

        $azimutLogin = $this->get('azimut_login_oauth_client');
        try {
            $azimutLogin->unauthorizeUser($user->toLoginUser());
        } catch (ClientException $e) {
            // If we get here, it's that an error happened
            // The only error we know of is 404, which means the user
            // has already been removed from Login, meaning we
            // can safely remove it locally
            if ($e->getCode() != Response::HTTP_NOT_FOUND) {
                throw $e;
            }
        }

        $userManager->deleteUser($user);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get user entity instance
    * @var integer $id Id of the user
    * @return User
    */

    protected function getUserEntity($id)
    {
        $um = $this->container->get('azimut.user_manager');
        $user = $um->findUserBy(array('id' => $id));

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User '.$id);
        }

        return $user;
    }

    /**
     * @Rest\QueryParam(name="email")
     * @param ParamFetcher $paramFetcher
     * @Rest\Get("searchLogin")
     * @return ALUser|bool
     */
    public function findUserFromLoginAction(ParamFetcher $paramFetcher)
    {
        $email = $paramFetcher->get('email');

        try {
            return $this->get('azimut_login_oauth_client')->getUserByEmailAddress($email);
        } catch (\InvalidArgumentException $e) {
            return new Response($e->getMessage(), 400);
        }
    }

    /**
     * @param User $user
     * @Rest\Post("/users/{user}/validationemail")
     * @return Response
     */
    public function postUserValidationEmailAction(User $user)
    {
        if ($user->isConfirmed()) {
            return new Response('User is already confirmed', 400);
        }
        $this->get('azimut_login_oauth_client')->reSendValidationEmail($user->toLoginUser());

        return new Response(null, 200);
    }
}
