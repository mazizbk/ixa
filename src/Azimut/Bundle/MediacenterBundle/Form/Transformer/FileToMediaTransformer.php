<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-11-19 16:26:05
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Transformer;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Component\Form\DataTransformerInterface;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Doctrine\ORM\EntityManager;

class FileToMediaTransformer implements DataTransformerInterface
{
    private $em;

    /**
    * @param EntityManager $em
    */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
    * {@inheritdoc}
    */
    public function reverseTransform($file)
    {
        if (null === $file) {
            return null;
        }

        $mediaRepository = $this->em->getRepository(Media::class);
        $mediaDeclinationRepository = $this->em->getRepository(MediaDeclination::class);

        $media = Media::createFromFile($file, $mediaRepository, $mediaDeclinationRepository);

        return $media;
    }

    /**
    * {@inheritdoc}
    */
    public function transform($media)
    {
        return $media;
    }
}
