<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-11-20 14:32:18
 */

namespace Azimut\Bundle\MediacenterBundle\Controller\Test;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @group azsystem
 */
class ApiMediaControllerTest extends WebTestCase
{
    private $apiUrl = '/api/mediacenter/media';

    private $fixturesDir;

    /**
     * @var Client
     */
    private $client;

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
        $this->fixturesDir = $this->client->getContainer()->getParameter('mediacenter_fixtures_dir');
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::postMediaAction
     */
    public function testPostMediaAction()
    {
        $name = 'My new media';
        $folderId = 1;

        copy($this->fixturesDir.'/img1.jpg', $this->fixturesDir.'/img1-tmp.jpg');

        $photo = new UploadedFile($this->fixturesDir.'/img1-tmp.jpg', 'photo.jpg', 'image/jpeg', 123);

        $this->client->request(
            'POST',
            $this->apiUrl,
            [
                'media' => [
                    'name' => $name,
                    'folder' => $folderId,
                    'type' => 'image',
                    'description' => [
                        'en' => 'my description',
                        'fr' => 'ma description',
                    ],
                    'mediaType' => [
                        'altText' => [
                            'en' => 'my alternate text',
                            'fr' => 'mon texte alternatif',
                        ]
                    ],
                    'mediaDeclinations' => [
                        0 => [
                            'name' => 'Original'
                        ]
                    ]
                ]
            ],
            [
                'media' => [
                    'mediaDeclinations' => [
                        0 => [
                            'file' => $photo
                        ]
                    ]
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
        $this->assertArrayHasKey('media', $data);

        $media = $data['media'];

        $this->assertArrayHasKey('id', $media);
        $this->assertArrayHasKey('folder', $media);
        $this->assertArrayHasKey('name', $media);
        $this->assertArrayHasKey('mediaType', $media);
        $this->assertArrayHasKey('description', $media);
        $this->assertArrayHasKey('altText', $media);
        $this->assertArrayHasKey('declinations', $media);

        // check data values
        $this->assertEquals($name, $media['name']);
        $this->assertEquals($folderId, $media['folder']['id']);

        return $media['id'];
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::postMediafromfileAction
     */
    public function testPostMediafromfileAction()
    {
        $folderId = 1;

        copy($this->fixturesDir.'/img2.jpg', $this->fixturesDir.'/img2-tmp.jpg');

        $photo = new UploadedFile($this->fixturesDir.'/img2-tmp.jpg', 'photo2.jpg', 'image/jpeg', 123);

        $this->client->request(
            'POST',
            $this->apiUrl.'fromfiles',
            [
                'simple_media' => [
                    'folder' => $folderId,
                ]
            ],
            [
                'simple_media' => [
                    'upload' => $photo
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
        $this->assertArrayHasKey('media', $data);

        $media = $data['media'];

        $this->assertArrayHasKey('id', $media);
        $this->assertArrayHasKey('folder', $media);
        $this->assertArrayHasKey('name', $media);
        $this->assertArrayHasKey('mediaType', $media);
        $this->assertArrayHasKey('description', $media);
        $this->assertArrayHasKey('altText', $media);
        $this->assertArrayHasKey('declinations', $media);

        // check data values
        $this->assertEquals('photo2', $media['name']);
        $this->assertEquals($folderId, $media['folder']['id']);
    }

    /**
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::getAllMediaAction
     */
    public function testGetAllMediaAction()
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
        $this->assertArrayHasKey('medias', $data);

        $medias = $data['medias'];

        $this->assertArrayHasKey(0, $medias);

        $this->assertArrayHasKey('id', $medias[0]);
        $this->assertArrayHasKey('name', $medias[0]);
        $this->assertArrayHasKey('mediaType', $medias[0]);
        $this->assertArrayHasKey('folderId', $medias[0]);
    }

    /**
     * @depends testPostMediaAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::getMediaAction
     */
    public function testGetMediaAction($mediaId)
    {
        $this->client->request(
            'GET',
            $this->apiUrl.'/'.$mediaId
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('media', $data);

        $media = $data['media'];

        $this->assertArrayHasKey('id', $media);
        $this->assertArrayHasKey('folder', $media);
        $this->assertArrayHasKey('name', $media);
        $this->assertArrayHasKey('mediaType', $media);
        $this->assertArrayHasKey('description', $media);
        $this->assertArrayHasKey('altText', $media);
        $this->assertArrayHasKey('declinations', $media);

        // check data values
        $this->assertEquals($mediaId, $media['id']);
    }

    /**
     * @depends testPostMediaAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::postMediaAction
     */
    public function testPostMediasActionWithoutMediaDatas()
    {
        $this->client->request(
            'POST',
            $this->apiUrl,
            [
                'name' => 'example'
            ]
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostMediaAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::putMediaAction
     */
    public function testPutMediaAction($mediaId)
    {
        $name = 'My new name';
        $folderId = 2;

        $this->client->request(
            'PUT',
            $this->apiUrl.'/'.$mediaId,
            [
                'media' => [
                    'name' => $name,
                    'folder' => $folderId,
                    'type' => 'image',
                    'description' => [
                        'en' => 'my new description',
                        'fr' => 'ma nouvelle description',
                    ],
                    'mediaType' => [
                        'altText' => [
                            'en' => 'my new alternate text',
                            'fr' => 'mon nouveau texte alternatif',
                        ]
                    ]
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('media', $data);

        $media = $data['media'];

        $this->assertArrayHasKey('id', $media);
        $this->assertArrayHasKey('folder', $media);
        $this->assertArrayHasKey('name', $media);
        $this->assertArrayHasKey('mediaType', $media);
        $this->assertArrayHasKey('description', $media);
        $this->assertArrayHasKey('altText', $media);
        $this->assertArrayHasKey('declinations', $media);

        // check data values
        $this->assertEquals($mediaId, $media['id']);
        $this->assertEquals($name, $media['name']);
        $this->assertEquals($folderId, $media['folder']['id']);
    }

    /**
     * @depends testPostMediaAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::patchMediaAction
     */
    public function testPatchMediaAction($mediaId)
    {
        $name = 'My new patched name';
        $folderId = 2;

        $this->client->request(
            'PATCH',
            $this->apiUrl.'/'.$mediaId,
            [
                'media' => [
                    'name' => $name,
                ]
            ]
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true);

        // check data structure
        $this->assertArrayHasKey('media', $data);

        $media = $data['media'];

        $this->assertArrayHasKey('id', $media);
        $this->assertArrayHasKey('folder', $media);
        $this->assertArrayHasKey('name', $media);
        $this->assertArrayHasKey('mediaType', $media);
        $this->assertArrayHasKey('description', $media);
        $this->assertArrayHasKey('altText', $media);
        $this->assertArrayHasKey('declinations', $media);

        // check data values
        $this->assertEquals($mediaId, $media['id']);
        $this->assertEquals($name, $media['name']);
        $this->assertEquals($folderId, $media['folder']['id']);
    }

    /**
     * @depends testPostMediaAction
     * @covers \Azimut\Bundle\MediacenterBundle\Controller\ApiMediaController::deleteMediaAction
     */
    public function testDeleteMediaAction($mediaId)
    {
        $this->client->request(
            'DELETE',
            $this->apiUrl.'/'.$mediaId
        );
        $response = $this->client->getResponse();

        // check that response is ok and is json
        $this->assertEquals(204, $response->getStatusCode());

        $this->client->request(
            'GET',
            $this->apiUrl.'/'.$mediaId
        );
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}
