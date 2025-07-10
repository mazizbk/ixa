<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-29 16:40:55
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_translation")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class MediaTranslation implements EntityTranslationInterface
{

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Media", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $media;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $description;

    public function getTranslatable()
    {
        return $this->media;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof Media) {
            throw new \RuntimeException('Expected $translatable to be an instance of Media');
        }

        $this->media = $translatable;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
