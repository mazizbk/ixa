<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-10-10 14:30:00
 */

namespace Azimut\Bundle\SecurityBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This listener handles ensures that for specific formats AccessDeniedExceptions
 * will return a 403 regardless of how the firewall is configured
 */
class AuthenticationListener extends ExceptionListener
{
    protected $securityContext;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param string $controller
     * @param LoggerInterface $logger
     */
    public function __construct(TokenStorageInterface $tokenStorage, $controller, LoggerInterface $logger = null)
    {
        $this->tokenStorage = $tokenStorage;
        parent::__construct($controller, $logger);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        $url = $request->getPathInfo();
        if (strpos($url, '/api') !== false) {
            if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationException) {
                $code = 403;
                $message = $exception->getMessage();

                if ($exception instanceof AuthenticationException) {
                    $code = 401;
                }

                if ((null === $token = $this->tokenStorage->getToken()) || ($token instanceof AnonymousToken)) {
                    $code = 401;
                    $message = 'You are not authenticated';
                }

                $event->setResponse(new JsonResponse(
                    array(
                        'error' => array(
                            'code' => $code,
                            'message' => $message
                        )
                    ),
                    $code
                ));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 6),
        );
    }
}
