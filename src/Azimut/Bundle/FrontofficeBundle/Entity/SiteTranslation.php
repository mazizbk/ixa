<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-09 15:59:08
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_site_translation")
 */
class SiteTranslation implements EntityTranslationInterface
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
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $site;

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
        return $this->site;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof Site) {
            throw new \RuntimeException('Expected $translatable to be an instance of Site');
        }

        $this->site = $translatable;

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
