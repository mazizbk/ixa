<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-03-12 15:33:09
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use HWI\Bundle\OAuthBundle\Controller\ConnectController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * OauthConnectController
 * This overide some behaviours of the connect controller of HWI OAuth
 */
class OAuthConnectController extends ConnectController
{
    /**
     * {@inheritdoc}
     */
    public function connectAction(Request $request)
    {
        parent::connectAction($request);

        $helper = $this->container->get('hwi_oauth.templating.helper.oauth');

        return $this->redirect(
            $helper->getLoginUrl('azimut_oauth')
        );
    }

    // /**
    //  * Display a message if there is no user logged in and connecting
    //  * is enabled.
    //  *
    //  * @param Request $request A request.
    //  * @param string  $key     Key used for retrieving the right information for the registration form.
    //  *
    //  * @return Response
    //  *
    //  * @throws NotFoundHttpException if `connect` functionality was not enabled
    //  * @throws AccessDeniedException if any user is authenticated
    //  * @throws \Exception
    //  */
    // public function registrationAction(Request $request, $key)
    // {
    //     $connect = $this->container->getParameter('hwi_oauth.connect');
    //     if (!$connect) {
    //         throw new NotFoundHttpException();
    //     }

    //     $hasUser = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    //     if ($hasUser) {
    //         throw new AccessDeniedException('Cannot connect already registered account.');
    //     }

    //     //TODO: register an account from oauth to client/application
    //      return new Response("<html><body>{'You are not allowed to access this application, please contact an administrator '}</body></html>");
    // }
}
