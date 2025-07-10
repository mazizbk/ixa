<?php

/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-17 15:57:13
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

trait CmsFileTranslationSeoMetaTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=160, nullable=true)
     */
    private $metaDescription;

    /**
     * Get metaTitle
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaTitle
     *
     * @param string|null $metaTitle
     *
     * @return self
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set metaDescription
     *
     * @param string|null $metaDescription
     *
     * @return self
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }
}
