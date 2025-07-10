<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-10-25 17:02:30
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Transformer;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Component\Form\DataTransformerInterface;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Doctrine\ORM\EntityManager;

class EmbedHtmlToMediaTransformer implements DataTransformerInterface
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
    public function reverseTransform($embedHtml)
    {
        if (null === $embedHtml) {
            return null;
        }

        $mediaRepository = $this->em->getRepository(Media::class);
        $mediaDeclinationRepository = $this->em->getRepository(MediaDeclination::class);

        $media = Media::createFromEmbedHtml($embedHtml, $mediaRepository, $mediaDeclinationRepository);

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
