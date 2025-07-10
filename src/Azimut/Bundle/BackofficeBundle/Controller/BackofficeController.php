<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-12 17:09:23
 */

namespace Azimut\Bundle\BackofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BackofficeController extends Controller
{
    public function indexAction()
    {
        $revisionFile = $this->getParameter('kernel.project_dir').'/REVISION';
        if(is_file($revisionFile)) {
            $version = trim(file_get_contents($revisionFile));
        }
        else {
            $version = 'dev';
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('AzimutBackofficeBundle:Backoffice:base.html.twig', [
            'version' => $version,
            'userId' => $user->getId()
        ]);
    }

    public function mainAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userGravatarHash = md5(mb_strtolower(trim($user->getUsername())));

        return $this->render('AzimutBackofficeBundle:Backoffice:main.angularjs.twig', array(
            'user' => $user,
            'userGravatarHash' => $userGravatarHash
        ));
    }
}
