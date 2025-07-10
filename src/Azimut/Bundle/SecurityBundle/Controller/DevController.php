<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-26 10:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\AccessRightSite;
use Azimut\Bundle\SecurityBundle\Entity\TestAzimut;
use Azimut\Bundle\SecurityBundle\Form\Type\AccessRightType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAcl;
use Azimut\Bundle\SecurityBundle\Form\Type\AccessRoleType;
use Azimut\Bundle\SecurityBundle\Form\Type\UserAccessRightType;
use Azimut\Bundle\SecurityBundle\Entity\Acl;

class DevController extends Controller
{
    public function indexAction()
    {
        return $this->render('AzimutSecurityBundle:Dev:index.html.twig');
    }

    public function formAccessRoleAction(Request $request)
    {
        $role = new AccessRole();

        $form = $this->createForm(new AccessRoleType(), $role)
            ->add('submit', 'submit')
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($role);
                $em->flush();

                return $this->redirectView(
                    $this->generateUrl(
                        'azimut_security_dev_role',
                        array('id' => $role->getId())
                        )
                    );
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_role.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formUserUpdateAction(Request $request)
    {

       /*
        // test for method getMergedRolesOnEntities() for access role service
        $service = $this->get('azimut_frontoffice.roles');
        $roles = $service->getMergedRolesOnEntities();
        */
            $doctrine = $this->getDoctrine()->getManager();
            //$repo = $doctrine->getRepository('Azimut\Bundle\SecurityBundle\Entity\AccessRight');
        $page = $doctrine->getRepository('Azimut\Bundle\FrontofficeBundle\Entity\Page')->find(1);
        $site = $doctrine->getRepository('Azimut\Bundle\FrontofficeBundle\Entity\Site')->find(1);
            //$currentUser =  $doctrine->getRepository('Azimut\Bundle\SecurityBundle\Entity\User')->find(4);
        //$this->get('voter')->prefetch('VIEW', $currentUser, [$page, $page1, $page2]);

        return new Response('<html><body></body></html>');

            //foreach($repo->getUserAccessRightsOn($currentUser, $page, 'VIEW') as $accessRight)
            //foreach($repo->getUserAccessRightsClass($currentUser, get_class($page)) as $accessRight)
            /*foreach($repo->getUserGlobalAccessRights($currentUser, 'VIEW') as $accessRight)
            {
                foreach($accessRight->getRoles() as $role)
                {

                }
            }*/
        /*
        $id = 1;
        $user = $this->getUserEntity($id);
        $um = $this->container->get('azimut.user_manager');

        $form = $this->createForm(UserUpdateType::class, $user, array('csrf_protection' => false))
            ->add('submit', 'submit')
        ;
        $form->setData($user);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $form->getData();
                $um->updateUser($user);

                return  new Response('<html><body> User updated with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_user_update.html.twig', array(
            'form' => $form->createView(),
        ));*/
    }

    /**
     * Private : get user entity instance
     * @var integer $id Id of the user
     * @return \Azimut\Bundle\SecurityBundle\Entity\User
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

    public function formAccessRightRoleAction(Request $request)
    {
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->createInstanceFromString('roles');

        $aright->setGroup($this->getDoctrine()->getRepository('AzimutSecurityBundle:Group')->find(1));
        $form = $this->createForm(AccessRightType::class, $aright, array('group' => true))
            ->add('submit', SubmitType::class)
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $aright = $this->uniqueAccessRight($request->request);

                die();

                $aright = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($aright);
                $em->flush();

                return new Response('<html><body> Access Right Role Created with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right_role.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formAccessRightAppRoleAction(Request $request)
    {
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->createInstanceFromString('app_roles');
        $aright->setUser($this->getDoctrine()->getRepository('AzimutSecurityBundle:User')->find(3));

        $form = $this->createForm(AccessRightType::class, $aright, array( 'user' => true))
            ->add('submit', SubmitType::class)
        ;

        $form->handleRequest($request);

        $aright = $this->userUniqueAccessRight($aright->getUser()->getId(), "app_roles", $request->request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($aright);
            $em->flush();

            return new Response('<html><body> Access Right App Role Created with success </body></html>');
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right_app_role.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    public function userClientUnlinkAction(Request $request)
    {
        $url = $this->container->getParameter('oauth_server_url');
        $token = $this->container->getParameter('client_token');
        $arr = explode("_", $this->container->getParameter('client_id'), 2);
        $clientId = $arr[0];
        $userId = 1;
        $result = $this->removeUserClientAccess($url, $userId, $clientId, $token);
        echo $result;
        die();
    }

    protected function removeUserClientAccess($url, $userId, $clientId, $token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url.'userclientaccesses');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $data = array(
            'userId' => $userId,
            'clientId' => $clientId,
            'clientToken' => $token,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

    public function userClientLinkAction(Request $request)
    {
        $url = $this->container->getParameter('oauth_server_url');
        $token = $this->container->getParameter('client_token');
        $arr = explode("_", $this->container->getParameter('client_id'), 2);
        $clientId = $arr[0];
        $userId = 1;
        $result = $this->linkUserClientInServer($url, $userId, $clientId, $token);
        echo $result;
        die();
    }

    protected function linkUserClientInServer($url, $userId, $clientId, $token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url.'userclientaccesses');
        curl_setopt($ch, CURLOPT_POST, 1);
        $data = array(
            'userId' => $userId,
            'clientId' => $clientId,
            'clientToken' => $token,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

    public function formAccessRightClassAction(Request $request)
    {
        $aright = new AccessRightClass();
        $aright->setGroup($this->getDoctrine()->getRepository('AzimutSecurityBundle:Group')->find(2));
        $form = $this->createForm(AccessRightType::class, $aright, array('group' => true, 'user' => false))
            ->add('submit', SubmitType::class)
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $aright = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($aright);
                $em->flush();

                return new Response('<html><body> Access Right Class Created with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formAccessRightAclAction(Request $request)
    {
        $site = $this->getDoctrine()->getRepository('AzimutFrontofficeBundle:Site')->find(1);
        $acl = new Acl(get_class($site), $site->getId());
        $aright = new AccessRightAcl();
        $acl->setNotEditable('type', true);
        $aright->addAcl($acl);

        $form = $this->createForm(AccessRightType::class, $aright, array('group' => true, 'user' => true))
            ->add('submit', SubmitType::class)
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $aright = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($acl);
                $em->persist($aright);
                $em->flush();

                return new Response('<html><body> Access Right Object Created with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right_acl.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formUserAccessRightAction(Request $request)
    {
        $user = $this->getUserEntity('2');

        $form = $this->createForm(UserAccessRightType::class, $user, array(
            'csrf_protection' => false,
            'method' => 'POST'
        ))
            ->add('submit', SubmitType::class)
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        // vous êtes ici !!!

            return new Response('<html><body> User updated with success </body></html>');
        }


        return $this->render('AzimutSecurityBundle:Dev:form_user_access_right.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formAccessRightObjectAction(Request $request)
    {
        // $object = $this->getDoctrine()
       //     ->getRepository('AzimutFrontofficeBundle:Site')->find(1);

        $aright = new AccessRightSite();
        //$aright->setObject($object);

        $form = $this->createForm(AccessRightType::class, $aright, array('group' => false, 'user' => true))
            ->add('submit', SubmitType::class)
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $aright = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($aright);
                $em->flush();

                return new Response('<html><body> Access Right Object Created with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right_object.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function formTestSubFormAction(Request $request)
    {
        $test = new TestAzimut();

        $form = $this->createForm('test_azimut', $test, array('csrf_protection' => false));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $test = $form->getData();

                exit;

                return new Response('<html><body> Test Object Created with success </body></html>');
            }
        }

        return $this->render('AzimutSecurityBundle:Dev:form_access_right_object.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function getAccessRightAppAction(Request $request)
    {
        $roleServices = $this->container->get('azimut_security.role_provider_chain')->getProviders();
        $em =  $this->getDoctrine()->getManager();
        $roleManager = $em->getRepository('AzimutSecurityBundle:AccessRole');
        foreach ($roleServices as $service) {
            $roles[] = $service->getRoles();
        }
        foreach ($roles as $role) {
            for ($i = 0; $i < sizeof($role); $i++) {
                $arr = explode("_", $role[$i]);
                if ($arr[1] == 'APP') {
                    $rolesOnApp[] = $role[$i];
                }
            }
        }
    }

    private function uniqueAccessRight($requestParams)
    {
        $type = $requestParams->get('access_right')['type'];
        if (empty($requestParams->get('access_right')['user'])) {
            $groupId = $requestParams->get('access_right')['group'];
            $aright = $this->groupUniqueAccessRight($groupId, $type, $requestParams);
        } else {
            $userId = $requestParams->get('access_right')['user'];
            $aright = $this->userUniqueAccessRight($userId, $type, $requestParams);
        }
        if ($aright == null) {
            $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->createInstanceFromString($type);
        }

        return $aright;
    }

    private function userUniqueAccessRight($userId, $type, $requestParams)
    {
        echo '<br>inside user unique Access Right</br>';
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->findAccessRightsByUser($userId, $type, $requestParams);
        return $aright;
    }

    private function groupUniqueAccessRight($groupId, $type, $requestParams)
    {
        echo '<br>inside group unique Access Right</br>';
        $aright = $this->getDoctrine()
            ->getRepository('AzimutSecurityBundle:AccessRight')
            ->findAccessRightsByGroup($groupId, $type, $requestParams);

        return $aright;
    }
}
