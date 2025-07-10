<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-02 11:30:23
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_translation", indexes={@ORM\Index(name="slug_idx", columns={"slug"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class CmsFileTranslation implements EntityTranslationInterface
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
     * @var CmsFile
     *
     * @ORM\ManyToOne(targetEntity="CmsFile", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $cmsFile;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    // main full slug depends on context (ex: in wich Site it is publish),
    // so it's not mapped and populated in controllers
    protected $canonicalPath;

    public function getTranslatable()
    {
        return $this->cmsFile;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof CmsFile) {
            throw new \RuntimeException('Expected $translatable to be an instance of CmsFile');
        }

        $this->cmsFile = $translatable;

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

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCanonicalPath()
    {
        return $this->canonicalPath;
    }

    public function setCanonicalPath($canonicalPath)
    {
        $this->canonicalPath = $canonicalPath;
        return $this;
    }

    public function getCmsFile()
    {
        return $this->cmsFile;
    }
}
