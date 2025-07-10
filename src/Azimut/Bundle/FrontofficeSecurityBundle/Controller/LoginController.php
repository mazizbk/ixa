<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 14:13:48
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Controller\AbstractFrontController;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\LoginType;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\LostPasswordType;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\ResetPasswordType;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserRegistrationType;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Form\FormError;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\ImpersonatedFrontofficeUserToken;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Azimut\Bundle\FrontofficeSecurityBundle\Security\FormAuthenticator;

class LoginController extends AbstractFrontController
{
    private function checkActiveUserLoginOnSite(Site $site)
    {
        if (!$site->hasUserLogin()) {
            throw $this->createNotFoundException("User login is not enabled on this site.");
        }
    }

    /**
     * Login
     * @param  Request $request
     * @param  string  $template Path of the view template to use
     * @param  string  $loginPath Route path to use in action form attribute
     * @param  string  $targetUrl Absolute route to redirect after login
     * @param  string  $targetUrl Absolute route to redirect after login
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request, SessionInterface $session, $template = null, $loginPath = null, $targetUrl = null, $targetFailUrl = null)
    {
        if ($this->getUser() instanceof UserInterface) {
            if (null == $targetUrl) {
                $targetUrl = $this->generateUrl('azimut_frontoffice', ['path' => $this->getUser() instanceof Consultant ? 'espace-consultant' : 'espace-client', '_locale' => $request->getLocale()]);
            }
            return $this->redirect($targetUrl);
        }

        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $exception = $this->get('security.authentication_utils')
            ->getLastAuthenticationError();

        $form = $this->createForm(LoginType::class);

        $session->set(FormAuthenticator::TARGET_PATH_SESSION_KEY, $targetUrl);

        if (null != $targetFailUrl) {
            $session->set(FormAuthenticator::TARGET_FAIL_PATH_SESSION_KEY, $targetFailUrl);
        }

        $template = $template ?: 'SiteLayout/'.($site->getLayout()->getLoginTemplate()?:'login.html.twig');

        return $this->render($template, [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('login'),
            'pageDescription' => '',
            'site' => $site,
            'form' => $form->createView(),
            'error' => $exception? $exception->getMessageKey():null,
            'loginPath' => $loginPath,
        ]);
    }

    public function lostPasswordAction(Request $request)
    {
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $form = $this->createForm(LostPasswordType::class);

        $emailSent = false;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(FrontofficeUser::class)->findOneByEmail($form->get('email')->getData());
            if (null != $user) {
                $this->sendPasswordResetEmail($user, $site);
                $emailSent = true;
            }
            else {
                $form->get('email')->addError(new FormError($this->get('translator')->trans('frontoffice.user.not.found')));
            }
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getLostPasswordTemplate()?:'lost_password.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('lost.password'),
            'pageDescription' => '',
            'site' => $site,
            'form' => $form->createView(),
            'emailSent' => $emailSent,
        ]);
    }

    /**
     * @Secure(roles="ROLE_FRONT_USER")
     */
    public function passwordChangeAction(Request $request)
    {
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        $passwordReseted = false;

        if (null != $user) {
            $form = $this->createForm(ResetPasswordType::class, $user);

            if ($form->handleRequest($request)->isValid()) {
                // Update a persisted property so events are triggered and password is updated from plainPassword
                $user->setUpdatedAt(new \DateTime);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $passwordReseted = true;
            }
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getPasswordChangeTemplate()?:'password_change.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('password.reset'),
            'pageDescription' => '',
            'site' => $site,
            'form' => isset($form)?$form->createView():null,
            'passwordReseted' => $passwordReseted
        ]);
    }

    public function passwordResetAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $tokenFound = false;
        $passwordReseted = false;
        $user = $em->getRepository(FrontofficeUser::class)->findOneByValidResetToken($token);

        if (null != $user) {
            $tokenFound = true;

            $form = $this->createForm(ResetPasswordType::class, $user);

            if ($form->handleRequest($request)->isValid()) {
                $user->setResetToken(null);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $passwordReseted = true;
            }

        }

        return $this->render('SiteLayout/'.($site->getLayout()->getPasswordResetTemplate()?:'password_reset.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('password.reset'),
            'pageDescription' => '',
            'site' => $site,
            'form' => isset($form)?$form->createView():null,
            'token' => $token,
            'tokenFound' => $tokenFound,
            'passwordReseted' => $passwordReseted
        ]);
    }

    public function registerAction(Request $request, $template = null, $registerPath = null)
    {
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $user = new FrontofficeUser();
        $user->isActive($site->getLayout()->isNewUserActive());

        if (null != $site->getLayout()->getNewUserRole()) {
            $user->setRoles([$site->getLayout()->getNewUserRole()]);
        }

        $form = $this->createForm(FrontofficeUserRegistrationType::class, $user);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('azimut_frontofficesecurity.mailer')->sendUserCredentialsMail($user, $request->getHost(), !$user->isActive());

            $this->sendConfirmEmail($user, $site);
        }

        $template = $template ?: 'SiteLayout/'.($site->getLayout()->getRegisterTemplate()?:'register.html.twig');

        return $this->render($template, [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('register'),
            'pageDescription' => '',
            'site' => $site,
            'user' => $user,
            'form' => $form->createView(),
            'registerPath' => $registerPath,
        ]);
    }

    /**
     * @Secure(roles="ROLE_FRONT_USER")
     */
    public function editProfileAction(Request $request)
    {
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(FrontofficeUserType::class, $user, [
            'with_email'            => false,
            'with_address'          => true,
            'with_delivery_address' => true,
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getEditProfileTemplate()?:'edit_profile.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('edit.your.user.profile'),
            'pageDescription' => '',
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    public function confirmEmailAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $user = $em->getRepository(FrontofficeUser::class)->findOneByConfirmEmailToken($token);

        if (null != $user) {
            $user->isEmailConfirmed(true);
            $em->flush();
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getConfirmEmailTemplate()?:'confirm_email.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('confirm.email'),
            'pageDescription' => '',
            'site' => $site,
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function impersonateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $impersonatedUserToken = $em->getRepository(ImpersonatedFrontofficeUserToken::class)->findOneByToken($token);

        if (null == $impersonatedUserToken) {
            throw $this->createNotFoundException('Impersonated user token not found.');
        }

        $tokenLifeTime = $this->getParameter('impersonate_user_token_life_time');
        if ((new \DateTime())->getTimestamp() - $impersonatedUserToken->getCreationDateTime()->getTimestamp() > $tokenLifeTime) {
            throw $this->createNotFoundException('Impersonated user token out of date.');
        }

        if ($request->getClientIp() != $impersonatedUserToken->getIp()) {
            throw $this->createNotFoundException('Client IP does not match impersonated token IP');
        }

        // create the frontoffice user token
        $firewall = 'frontoffice';
        $roles = $impersonatedUserToken->getImpersonatedUser()->getRoles();
        $securityToken = new PostAuthenticationGuardToken($impersonatedUserToken->getImpersonatedUser(), $firewall, array_merge($roles, ['ROLE_FRONT_USER_IMPERSONATED']));
        $this->get('security.token_storage')->setToken($securityToken);

        $impersonatedUserToken->setUsageDateTime(new \DateTime);

        // invalidate impersonation token
        $impersonatedUserToken->setToken(null);

        $em->flush();

        if(in_array(Consultant::ROLE_DEFAULT, $roles)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => $this->get('twig.extension.page')->findPageByTemplate([], 'ixa/consultantarea.html.twig', $site)->getFullSlug(),]);
        }

        return $this->redirectToRoute('azimut_frontofficesecurity_home', ['_locale' => $request->getLocale()]);
    }

    public function homeAction(Request $request)
    {
        $site = $this->getSite($request);
        $this->checkActiveUserLoginOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getPostLoginTemplate()?:'post_login.html.twig'), [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('member'),
            'pageDescription' => '',
            'site' => $site,
        ]);
    }

    private function sendPasswordResetEmail(FrontofficeUser $user, Site $site)
    {
        $token = bin2hex(random_bytes(10));

        $em = $this->getDoctrine()->getManager();
        $user->setResetToken($token);
        $em->flush();

        $this->get('azimut_frontofficesecurity.mailer')->sendPasswordResetEmail($user, $site->getMainDomainName(), $token);
    }

    private function sendConfirmEmail(FrontofficeUser $user, Site $site)
    {
        $token = bin2hex(random_bytes(10));

        $em = $this->getDoctrine()->getManager();
        $user->setConfirmEmailToken($token);
        $em->flush();

        $this->get('azimut_frontofficesecurity.mailer')->sendConfirmEmailAddressMail($user, $site->getMainDomainName(), $token);
    }
}
