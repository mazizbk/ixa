<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-10 16:33:09
 */

namespace Azimut\Bundle\DemoAngularJsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_DEMO_ANGULAR_JS')")
 */
class BackofficeController extends Controller
{
    // Put here the actions for generating angularjs templates.
    // If no specific action is require in controller then prefer using
    // generic AzimutSecurityBundle:Template:template controller
}
