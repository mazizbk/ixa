<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-25 11:43:32
 */

namespace Azimut\Bundle\CmsBundle\Tests\Services;

use Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser;
use Azimut\Bundle\MediacenterBundle\Entity\MediaImage;
use Prophecy\Prophet;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage;

/**
 * @group azsystem
 */
class MediaDeclinationTagParserTest extends \PHPUnit_Framework_TestCase
{
    private $prophet;

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $registryMock;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManagerMock;

    /**
     * @var \Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaDeclinationRepository
     */
    private $repositoryMock;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $urlGeneratorMock;

    /**
     * @var MediaDeclinationTagParser
     */
    private $parser;

    public function setUp()
    {
        $this->prophet = new Prophet();

        $this->registryMock = $this->prophet->prophesize('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->entityManagerMock = $this->prophet->prophesize('Doctrine\ORM\EntityManagerInterface');
        $this->repositoryMock = $this->prophet->prophesize('Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaDeclinationRepository');
        $this->urlGeneratorMock = $this->prophet->prophesize('Symfony\Component\Routing\Generator\UrlGeneratorInterface');

        $filterSets = [
            'xs' => [
                'filters' => [
                    'thumbnail' => [
                        'size' => [150, 100]
                    ]
                ]
            ],
            's' => [
                'filters' => [
                    'thumbnail' => [
                        'size' => [300, 200]
                    ]
                ]
            ],
            'm' => [
                'filters' => [
                    'thumbnail' => [
                        'size' => [640, 426]
                    ]
                ]
            ],
            'xl' => [
                'filters' => [
                    'thumbnail' => [
                        'size' => [1280, 853]
                    ]
                ]
            ]
        ];

        $this->parser = new MediaDeclinationTagParser($this->registryMock->reveal(), $this->urlGeneratorMock->reveal(), $filterSets);
    }

    /**
     * @covers \Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser::parse
     */
    public function testParse()
    {
        $mediaDeclination = new MediaDeclinationImage();
        $this->setPropertyValue($mediaDeclination, 'id', 12);
        $mediaDeclination->setMedia((new MediaImage())->setAltText('my image'));
        $mediaDeclination->setPath('my-image.jpg');
        $mediaDeclination->setName('my image');

        $this->registryMock->getManager()->willReturn($this->entityManagerMock);
        $this->entityManagerMock->getRepository('AzimutMediacenterBundle:MediaDeclination')->willReturn($this->repositoryMock);
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy', ['filepath' => 'my-image.jpg'])->willReturn('/mediacenter/uploads/my-image.jpg');
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy_thumb', ['filepath' => 'my-image.jpg', 'size' => 'xl'])->willReturn('/mediacenter/uploads/xl/my-image.jpg');
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy_thumb', ['filepath' => 'my-image.jpg', 'size' => 'm'])->willReturn('/mediacenter/uploads/m/my-image.jpg');

        $this->repositoryMock->find(12)->willReturn($mediaDeclination);



        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12 | {"width": "120", "height": "50"} ## Vestibulum lobortis vestibulum est');
        $this->assertEquals('Pellentesque ac risus fringilla <figure class="Figure"><img src="/mediacenter/uploads/m/my-image.jpg" alt="My image" class="Figure-image" width="120" height="50" /></figure> Vestibulum lobortis vestibulum est', $parsedValue);

        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12 | {"width": "120"} ## Vestibulum lobortis vestibulum est');
        $this->assertEquals('Pellentesque ac risus fringilla <figure class="Figure"><img src="/mediacenter/uploads/m/my-image.jpg" alt="My image" class="Figure-image" width="120" /></figure> Vestibulum lobortis vestibulum est', $parsedValue);

        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12 ## Vestibulum lobortis vestibulum est');
        $this->assertEquals('Pellentesque ac risus fringilla <figure class="Figure"><img src="/mediacenter/uploads/xl/my-image.jpg" alt="My image" class="Figure-image" /></figure> Vestibulum lobortis vestibulum est', $parsedValue);

        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12 | {"width": "120", "style": "float: left", "class": "myClass"} ## Vestibulum lobortis vestibulum est');
        $this->assertEquals('Pellentesque ac risus fringilla <figure class="myClass Figure"><img src="/mediacenter/uploads/m/my-image.jpg" alt="My image" class="Figure-image" width="120" style="float: left" /></figure> Vestibulum lobortis vestibulum est', $parsedValue);
    }

    /**
     * @covers \Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser::parse
     */
    public function testParseWhenMultipleTagsInText()
    {
        $mediaDeclination = new MediaDeclinationImage();
        $this->setPropertyValue($mediaDeclination, 'id', 12);
        $mediaDeclination->setPath('my-image.jpg');
        $mediaDeclination->setName('my image');
        $mediaDeclination->setMedia((new MediaImage())->setAltText('my image'));

        $mediaDeclination2 = new MediaDeclinationImage();
        $this->setPropertyValue($mediaDeclination2, 'id', 8);
        $mediaDeclination2->setPath('my-other-image.jpg');
        $mediaDeclination2->setName('my other image');
        $mediaDeclination2->setMedia((new MediaImage())->setAltText('my other image'));

        $this->registryMock->getManager()->willReturn($this->entityManagerMock);
        $this->entityManagerMock->getRepository('AzimutMediacenterBundle:MediaDeclination')->willReturn($this->repositoryMock);
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy', ['filepath' => 'my-image.jpg'])->willReturn('/mediacenter/uploads/my-image.jpg');
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy', ['filepath' => 'my-other-image.jpg'])->willReturn('/mediacenter/uploads/my-other-image.jpg');
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy_thumb', ['filepath' => 'my-image.jpg', 'size' => 'xl'])->willReturn('/mediacenter/uploads/xl/my-image.jpg');
        $this->urlGeneratorMock->generate('azimut_mediacenter_file_proxy_thumb', ['filepath' => 'my-other-image.jpg', 'size' => 'xl'])->willReturn('/mediacenter/uploads/xl/my-other-image.jpg');

        $this->repositoryMock->find(12)->willReturn($mediaDeclination);

        $this->repositoryMock->find(8)->willReturn($mediaDeclination2);

        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12 ## Vestibulum lobortis vestibulum est ## media-declination-8 ## Nulla ornare gravida eros');
        $this->assertEquals('Pellentesque ac risus fringilla <figure class="Figure"><img src="/mediacenter/uploads/xl/my-image.jpg" alt="My image" class="Figure-image" /></figure> Vestibulum lobortis vestibulum est <figure class="Figure"><img src="/mediacenter/uploads/xl/my-other-image.jpg" alt="My other image" class="Figure-image" /></figure> Nulla ornare gravida eros', $parsedValue);
    }

    /**
     * @covers \Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser::parse
     */
    public function testParseWhenMediaDeclinationDoNotExists()
    {
        $this->registryMock->getManager()->willReturn($this->entityManagerMock);
        $this->entityManagerMock->getRepository('AzimutMediacenterBundle:MediaDeclination')->willReturn($this->repositoryMock);

        $this->repositoryMock->find(12345)->willReturn(null);

        $parsedValue = $this->parser->parse('Pellentesque ac risus fringilla ## media-declination-12345 ## Vestibulum lobortis vestibulum est');
        $this->assertEquals('Pellentesque ac risus fringilla  Vestibulum lobortis vestibulum est', $parsedValue);
    }

    private function setPropertyValue($object, $property, $value)
    {
        $refObject   = new \ReflectionObject($object);
        $refProperty = $refObject->getProperty($property);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
}
