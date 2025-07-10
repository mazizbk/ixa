<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:56:14
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @PreAuthorize("isAuthenticated()")
 */
class DashboardController extends Controller
{
    public function widgetsAction()
    {
        if (!$this->isGranted('APP_SECURITY')) {
            return new Response();
        }

        $usersCount = $this->getDoctrine()->getRepository(User::class)->getUsersCount();

        return $this->render('AzimutSecurityBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'usersCount' => $usersCount,
        ]);
    }
}
