<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-26 12:16:47
 */

namespace Azimut\Bundle\CmsMapBundle\Controller;

use Azimut\Bundle\CmsBundle\Controller\ApiPublicCmsFileController;

class ApiPublicMapPointController extends ApiPublicCmsFileController
{
    protected static $rootPropertySingleName = 'point';
    protected static $rootPropertyPluralName = 'points';
}
