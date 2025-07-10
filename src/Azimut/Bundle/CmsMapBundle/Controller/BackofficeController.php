<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:05:33
 */

namespace Azimut\Bundle\CmsMapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_CMS_MAP')")
 */
class BackofficeController extends Controller
{

}
