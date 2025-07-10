<?php

/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-17 15:57:13
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

trait CmsFileSeoMetaTrait
{
    /**
     * @var  bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_cms_file"})
     */
    protected $autoMetas = true;

    /**
     * Has auto metas
     *
     * @return boolean
     */
    public function hasAutoMetas()
    {
        return $this->autoMetas;
    }

    /**
     * Set auto meta
     *
     * @param bool $autoMetas
     *
     * @return self
     */
    public function setAutoMetas($autoMetas)
    {
        $this->autoMetas = $autoMetas;
        return $this;
    }

    /**
     * Get meta title
     *
     * @param string|null $locale
     *
     * @return string|null
     *
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getMetaTitle($locale = null)
    {
        if ($this->hasAutoMetas()) {
            return $this->getName($locale);
        }
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getMetaTitle();
    }

    public function setMetaTitle($metaTitle, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setMetaTitle($metaTitle);

        return $this;
    }

    /**
     * Get meta description
     *
     * @param string|null $locale
     *
     * @return string|null
     *
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getMetaDescription($locale = null)
    {
        if ($this->hasAutoMetas()) {
            return $this->getAbstract($locale);
        }
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getMetaDescription();
    }

    public function setMetaDescription($metaDescription, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setMetaDescription($metaDescription);

        return $this;
    }
}
