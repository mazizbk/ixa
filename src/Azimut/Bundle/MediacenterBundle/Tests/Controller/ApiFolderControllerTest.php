<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-11-17 17:39:33
 */

namespace Azimut\Bundle\MediacenterBundle\Controller\Test;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * @group azsystem
 */
class ApiFolderControllerTest extends WebTestCase
{
    private $apiUrl = '/api/mediacenter/folders';

    /**
     * @var Client
     */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();

        $session = $this->client->getContainer()->get('session');

        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        $token = new OAuthToken([
            'access_token' => 'mockeduptoken',
            'expires_in' => '3600',
        ], ['ROLE_USER', 'IS_AUTHENTICATED_FULLY']);

        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::postFoldersAction
     */
    public function testPostRootFolderAction()
    {
        $name = 'My new root folder';

        $this->client->request(
            'POST',
            $this->apiUrl,
            [
                'folder' => [
                    'name' => $name
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that we have a redirection
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Location'));

        // follow the redirection
        $this->client->request('GET', $response->headers->get('Location'));
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folder', $data);

        $folder = $data['folder'];

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('medias', $folder);

        // check data values
        $this->assertEquals($name, $folder['name']);

        return $folder['id'];
    }

    /**
     * @depends testPostRootFolderAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::postFoldersAction
     */
    public function testPostFoldersAction($parentFolderId)
    {
        $name = 'My new folder';

        $this->client->request(
            'POST',
            $this->apiUrl,
            [
                'folder' => [
                    'name' => $name,
                    'parentFolder' => $parentFolderId
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that we have a redirection
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Location'));

        // follow the redirection
        $this->client->request('GET', $response->headers->get('Location'));
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folder', $data);

        $folder = $data['folder'];

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('parentFolderId', $folder);
        $this->assertArrayHasKey('medias', $folder);

        // check data values
        $this->assertEquals($name, $folder['name']);
        $this->assertEquals($parentFolderId, $folder['parentFolderId']);

        return $folder['id'];
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::getFoldersAction
     */
    public function testGetFoldersAction()
    {
        $this->client->request(
            'GET',
            $this->apiUrl
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folders', $data);

        $folders = $data['folders'];

        $this->assertArrayHasKey(0, $folders);

        $this->assertArrayHasKey('id', $folders[0]);
        $this->assertArrayHasKey('name', $folders[0]);
        $this->assertArrayHasKey('subfolders', $folders[0]);
        $this->assertArrayNotHasKey('medias', $folders[0]);
    }

    /**
     * @depends testPostFoldersAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::postFoldersAction
     */
    public function testGetFolderAction($folderId)
    {
        $this->client->request(
            'GET',
            $this->apiUrl.'/'.$folderId
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folder', $data);

        $folder = $data['folder'];

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('parentFolderId', $folder);
        $this->assertArrayHasKey('medias', $folder);

        // check data values
        $this->assertEquals($folderId, $folder['id']);
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::postFoldersAction
     */
    public function testPostFoldersActionWithoutFolderDatas()
    {
        $this->client->request(
            'POST',
            $this->apiUrl,
            [
                'name' => 'example',
                'parentFolder' => 1
            ]
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostFoldersAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::putFolderAction
     */
    public function testPutFolderAction($folderId)
    {
        $name = 'My new name';
        $parentFolderId = 1;

        $this->client->request(
            'PUT',
            $this->apiUrl.'/'.$folderId,
            [
                'folder' => [
                    'name' => $name,
                    'parentFolder' => $parentFolderId
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folder', $data);

        $folder = $data['folder'];

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('parentFolderId', $folder);
        $this->assertArrayHasKey('medias', $folder);

        // check data values
        $this->assertEquals($folderId, $folder['id']);
        $this->assertEquals($name, $folder['name']);
        $this->assertEquals($parentFolderId, $folder['parentFolderId']);
    }

    /**
     * @depends testPostFoldersAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::patchFolderAction
     */
    public function testPatchFolderAction($folderId)
    {
        $name = 'A folder new name';
        $parentFolderId = 1;

        $this->client->request(
            'PATCH',
            $this->apiUrl.'/'.$folderId,
            [
                'folder' => [
                    'name' => $name
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('folder', $data);

        $folder = $data['folder'];

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('parentFolderId', $folder);
        $this->assertArrayHasKey('medias', $folder);

        // check data values
        $this->assertEquals($folderId, $folder['id']);
        $this->assertEquals($name, $folder['name']);
        $this->assertEquals($parentFolderId, $folder['parentFolderId']);
    }

    /**
     * @depends testPostFoldersAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiFolderController::deleteFolderAction
     */
    public function testDeleteFolderAction($folderId)
    {
        $this->client->request(
            'DELETE',
            $this->apiUrl.'/'.$folderId
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(204, $response->getStatusCode());

        $this->client->request(
            'GET',
            $this->apiUrl.'/'.$folderId
        );
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}
