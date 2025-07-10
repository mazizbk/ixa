<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-10-30 10:26:35
 */

namespace Azimut\Bundle\CmsContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_CMS_CONTACT')")
 */
class BackofficeController extends Controller
{

}
