<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-10-13 11:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\TemplateController as BaseController;

class TemplateController extends BaseController
{
    /**
     * Renders a template.
     *
     * @param string    $template The template name
     * @param null      $roles
     * @param int|null  $maxAge Max age for client caching
     * @param int|null  $sharedAge Max age for shared (proxy) caching
     * @param bool|null $private Whether or not caching should apply for client caches only
     * @return Response A Response instance
     */
    public function templateAction($template, $roles = null, $maxAge = null, $sharedAge = null, $private = null)
    {
        if (null == $maxAge) {
            $maxAge = $this->container->getParameter('angularjs_template_max_age');
        }

        $authorization_checker = $this->container->get('security.authorization_checker');
        if ($authorization_checker->isGranted('IS_AUTHENTICATED_FULLY') || $authorization_checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $isGranted = true;

            if ($roles != null && count($roles) > 0) {
                $isGranted = false;
                if ($authorization_checker->isGranted('SUPER_ADMIN')) {
                    $isGranted = true;
                } else {
                    foreach ($roles as $role) {
                        if ($authorization_checker->isGranted($role)) {
                            $isGranted = true;
                        }
                    }
                }
            }

            if (!$isGranted) {
                return $this->container->get('templating')->renderResponse('AzimutSecurityBundle:Backoffice:error_access.angularjs.twig');
            }

            $sharedAge = null;
            $private = true;

            return parent::templateAction($template, $maxAge, $sharedAge, $private);
        } else {
            return $this->container->get('templating')->renderResponse('AzimutSecurityBundle:Backoffice:error_access.angularjs.twig');
        }
    }
}
