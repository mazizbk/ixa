<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-28 11:28:14
 */

namespace Azimut\Bundle\DemoAngularJsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_DEMO_ANGULAR_JS')")
 */
class ApiDemoController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_files"})
     *
     * @ApiDoc(
     *  section="Demo AngularJS",
     *  resource=true,
     *  description="Get all files"
     * )
     */
    public function getFilesAction(Request $request)
    {
        return [
            'files' => [
                'demo file 1',
                'demo file 2'
            ]
        ];
    }
}
