<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-14 17:27:41
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Security;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Translation\TranslatorInterface;

class FormAuthenticator extends AbstractGuardAuthenticator
{
    const TARGET_PATH_SESSION_KEY = '_security.frontoffice.target_path';
    const TARGET_FAIL_PATH_SESSION_KEY = '_security.frontoffice.target_fail_path';

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Default message for authentication failure.
     *
     * @var string
     */
    private $failMessage = 'invalid.credentials';

    /**
     * Creates a new instance of FormAuthenticator
     */
    public function __construct(UserPasswordEncoder $passwordEncoder, RouterInterface $router, TranslatorInterface $translator) {
        $this->passwordEncoder = $passwordEncoder;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        if ('/login' != substr($request->getPathInfo(), -6) || !$request->isMethod('POST')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        return [
            'username' => $request->request->get('login')['username'],
            'password' => $request->request->get('login')['password'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            return $userProvider->loadUserByUsername($credentials['username']);
        }
        catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans($this->failMessage));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            return true;
        }
        throw new CustomUserMessageAuthenticationException($this->translator->trans($this->failMessage));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $requestedUri = $request->getSession()->get(self::TARGET_PATH_SESSION_KEY);
        if ($requestedUri) {
            return new RedirectResponse($requestedUri);
        }
        $user = $token->getUser();
        return new RedirectResponse($this->router->generate('azimut_frontoffice', ['path' => $user instanceof Consultant ? 'espace-consultant' : 'espace-client',]));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        $requestedUri = $request->getSession()->get(self::TARGET_FAIL_PATH_SESSION_KEY);
        if ($requestedUri) {
            return new RedirectResponse($requestedUri);
        }

        return new RedirectResponse($this->router->generate('azimut_frontofficesecurity_login'));
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('azimut_frontofficesecurity_login'));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
