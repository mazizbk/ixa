<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-09 14:26:02
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_zone_translation")
 */
class ZoneTranslation implements EntityTranslationInterface
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
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="Zone", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $zone;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $title;

    public function getTranslatable()
    {
        return $this->zone;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof Zone) {
            throw new \RuntimeException('Expected $translatable to be an instance of Zone');
        }

        $this->zone = $translatable;

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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
