<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 10:54:34
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Controller;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\ImpersonatedFrontofficeUserToken;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE_SECURITY')")
 */
class ApiFrontofficeUserController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_frontoffice_users"})
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  resource=true,
     *  description="Frontoffice security : Get all front users"
     * )
     */
    public function getUsersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(FrontofficeUser::class)->findAll();

        return [
            'users' => $users,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the front user
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "detail_frontoffice_user"})
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Get a front user"
     * )
     */
    public function getUserAction($id)
    {
        $user = $this->getFrontofficeUserEntity($id);

        return [
            'user' => $user,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Create new front user",
     *  input="Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType",
     *  output="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser"
     * )
     */
    public function postUsersAction(Request $request)
    {
        if (!$request->request->get('frontoffice_user')) {
            throw new BadRequestHttpException("Frontoffice user not found in posted datas.");
        }

        $user = new FrontofficeUser();

        $form = $this->createForm(FrontofficeUserType::class, $user, [
            'csrf_protection'    => false,
            'with_password'      => true,
            'with_access_rights' => true,
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('azimut_frontofficesecurity.mailer')->sendUserCredentialsMail($user, $request->getHost(), !$user->isActive());

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_frontofficesecurity_api_get_user',
                    ['id' => $user->getId()]
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
     * @var integer $id Id of the front user
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_frontoffice_user"})
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Update front user",
     *  input="Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType",
     *  output="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser"
     * )
     */
    public function putUserAction(Request $request, $id)
    {
        if (!$request->request->get('frontoffice_user')) {
            throw new BadRequestHttpException("FrontofficeUser not found in posted datas.");
        }

        $user = $this->getFrontofficeUserEntity($id);

        $form = $this->createForm(FrontofficeUserType::class, $user, [
            'method'             => 'PUT',
            'with_access_rights' => true,
            'csrf_protection'    => false,
        ]);

        return $this->updateFrontofficeUser($request, $user, $form, $id);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the front user
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_frontoffice_user"})
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Update front user (only fields that are submitted)",
     *  input="Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType",
     *  output="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser"
     * )
     */
    public function patchUserAction(Request $request, $id)
    {
        if (!$request->request->get('frontoffice_user')) {
            throw new BadRequestHttpException("FrontofficeUser not found in posted datas.");
        }

        $user = $this->getFrontofficeUserEntity($id);

        $form = $this->createForm(FrontofficeUserType::class, $user, [
            'method'             => 'PATCH',
            'with_access_rights' => true,
            'csrf_protection'    => false,
        ]);

        return $this->updateFrontofficeUser($request, $user, $form, $id);
    }

    /**
     * Delete action
     * @var integer $id Id of the front user
     * @return View
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Delete front user"
     * )
     */
    public function deleteUserAction($id)
    {
        $user = $this->getFrontofficeUserEntity($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @var integer $id Id of the entity
     * @return FrontofficeUser
     */
    protected function getFrontofficeUserEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(FrontofficeUser::class)->findOneById($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find front user '.$id);
        }

        return $user;
    }

    protected function updateFrontofficeUser($request, $user, FormInterface $form, $id)
    {
        $wasActive = $user->isActive();

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // force doctrine to reload from DB
            // (because of some collections not being reindexed)
            $em->clear();
            $user = $this->getFrontofficeUserEntity($id);

            if (!$wasActive && $user->isActive()) {
                $this->get('azimut_frontofficesecurity.mailer')->sendUserActivatedMail($user, $request->getHost());
            }

            return [
                'user' => $user,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Impersonate user
     * @var integer $id Id of the front user
     * @return array
     *
     * @ApiDoc(
     *  section="Frontoffice security",
     *  description="Frontoffice security : Connect as other user",
     * )
     */
    public function postImpersonateAction(Request $request, $id)
    {
        if (!($this->isGranted('SUPER_ADMIN') || $this->getParameter('allow_front_user_impersonation') && $this->isGranted('GLOBAL_IMPERSONATE_USER'))) {
            throw $this->createAccessDeniedException();
        }

        $frontUser = $this->getFrontofficeUserEntity($id);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $impersonateToken = new ImpersonatedFrontofficeUserToken();
        $token = bin2hex(random_bytes(10));
        $impersonateToken
            ->setToken($token)
            ->setCreationDateTime(new \DateTime())
            ->setIp($request->getClientIp())
            ->setLoggedUser($user)
            ->setImpersonatedUser($frontUser)
        ;
        $em->persist($impersonateToken);
        $em->flush();

        return [
            'token' => $token,
        ];
    }
}
