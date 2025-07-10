<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-07 14:57:38
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $locale = $request->getLocale();
        $response = new RedirectResponse($this->generateUrl($locale.'__RG__azimut_security_oauth_login'));

        return $response;
    }

    public function loginCheckAction(Request $request)
    {
    }

    public function logoutAction(Request $request)
    {
        $response = new Response();

        if ($request->getMethod() != Request::METHOD_HEAD) {
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
            if (!$request->query->has('noredirect')) {
                $response = new RedirectResponse($this->container->getParameter('oauth_server_logout_url'));
            }
        }

        $base_url_login = $this->getParameter('base_url_login');
        $parsed = parse_url($base_url_login);
        $response->headers->add([
            'Access-Control-Allow-Origin' => $parsed['scheme'].'://'.$parsed['host'],
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        return $response;
    }
}
