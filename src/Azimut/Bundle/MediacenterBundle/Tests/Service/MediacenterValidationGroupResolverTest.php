<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-12-14 10:32:29
 */

namespace Azimut\Bundle\MediacenterBundle\Service\Test;

use Azimut\Bundle\MediacenterBundle\Service\MediacenterValidationGroupResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group azsystem
 */
class MediacenterValidationGroupResolverTest extends WebTestCase
{
    /**
     * @var MediacenterValidationGroupResolver
     */
    private $groupResolver;

    public function setUp()
    {
        $this->groupResolver = new MediacenterValidationGroupResolver();
    }

    public function tearDown()
    {
        $this->groupResolver = null;
    }


    private function generateMediaRequest(array $data)
    {
        return new Request(
            [],
            [
                'media' => $data
            ]
        );
    }

    private function generateMediaDeclinationRequest(array $data)
    {
        return new Request(
            [],
            [
                'media_declination' => $data
            ]
        );
    }

    public function testMediaImageDeclination()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'image'
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaVideoDeclination()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'video'
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaDeclinationVideoEmbeddedMediaChecked()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'video',
            'mediaDeclinationType' => [
                'isEmbeddedMedia' => 'true'
            ]
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['embedHtmlRequired']
        );
    }

    public function testMediaDeclinationVideoEmbeddedMediaUnChecked()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'video',
            'mediaDeclinationType' => [
                'isEmbeddedMedia' => 'false'
            ]
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaDeclinationVideoEmbeddedMediaNotPresent()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'video',
            'mediaDeclinationType' => [
            ]
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaDeclinationVideoEmbeddedMediaCheckedForcedType()
    {
        $request = $this->generateMediaDeclinationRequest([
            'mediaDeclinationType' => [
                'isEmbeddedMedia' => 'true'
            ]
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request, 'video'),
            ['embedHtmlRequired']
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMediaDeclinationVideoEmbeddedMediaNoTypeFound()
    {
        $request = $this->generateMediaDeclinationRequest([
            'mediaDeclinationType' => [
                'isEmbeddedMedia' => 'true'
            ]
        ]);

        $this->groupResolver->getGroups($request);
    }

    public function testMediaImageDeclinationUpdate()
    {
        $request = $this->generateMediaDeclinationRequest([
            'type' => 'image'
        ]);

        $request->setMethod(Request::METHOD_PUT);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            []
        );
    }






    public function testMediaImage()
    {
        $request = $this->generateMediaRequest([
            'type' => 'image'
        ]);


        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaVideo()
    {
        $request = $this->generateMediaRequest([
            'type' => 'video'
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }

    public function testMediaVideoEmbeddedMediaChecked()
    {
        $request = $this->generateMediaRequest([
            'type' => 'video',
            'mediaDeclinations' => [
                '0' => [
                    'mediaDeclinationType' => [
                        'isEmbeddedMedia' => 'true'
                    ]
                ]
            ],
        ]);

        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['embedHtmlRequired']
        );
    }

    public function testMediaVideoEmbeddedMediaUnChecked()
    {
        $request = $this->generateMediaRequest([
            'type' => 'video',
            'mediaDeclinations' => [
                '0' => [
                    'mediaDeclinationType' => [
                        'isEmbeddedMedia' => 'false'
                    ]
                ]
            ],
        ]);


        $this->assertEquals(
            $this->groupResolver->getGroups($request),
            ['fileRequired']
        );
    }
}
