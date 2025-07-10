<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-06
 */

namespace Azimut\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HelloController extends Controller
{
    public function indexAction()
    {
        return $this->render('AzimutDemoBundle::hello.html.twig');
    }
}
