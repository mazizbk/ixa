<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-03 17:08:02
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class TemplateController extends Controller
{
    public function displayLayoutPreviewAction($template)
    {
        if (!preg_match('/^[a-z0-9-_\/]+$/', $template)) {
            throw $this->createNotFoundException(sprintf('Invalid template format. Expected letters, digits, -, _, /, got "%s".', $template));
        }

        $templateIdentifier = 'PageLayoutPreview/'.$template.'.angularjs.twig';

        try {
            return $this->render($templateIdentifier);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException(sprintf('Template identified "%s" not found.', $template), $e);
        }
    }
}
