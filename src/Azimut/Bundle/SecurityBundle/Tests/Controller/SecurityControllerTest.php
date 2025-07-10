<?php
/**
 * Created by PhpStorm.
 * User: gerdald
 * Date: 24/07/14
 * Time: 10:44
 */

namespace Azimut\Bundle\SecurityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group azsystem
 */
class SecurityControllerTest extends WebTestCase
{
    public function setUp()
    {
        //start the symfony kernel
        $this->getKernelClass();
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        //get the DI container
        $this->container = $kernel->getContainer();
    }

    /**
     * @covers \Azimut\Bundle\SecurityBundle\Controller\SecurityController::loginAction
     */
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/admin/login');
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful() || $client->getResponse()->isRedirection());
    }
}
