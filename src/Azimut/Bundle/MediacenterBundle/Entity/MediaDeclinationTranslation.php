<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-29 18:12:05
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_translation")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class MediaDeclinationTranslation implements EntityTranslationInterface
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
     * @var MediaDeclination
     *
     * @ORM\ManyToOne(targetEntity="MediaDeclination", inversedBy="translations")
     */
    private $mediaDeclination;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    public function getTranslatable()
    {
        return $this->mediaDeclination;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof MediaDeclination) {
            throw new \RuntimeException('Expected $translatable to be an instance of MediaDeclination');
        }

        $this->mediaDeclination = $translatable;

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
}
