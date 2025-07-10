<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-25 14:59:38
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\FrontofficeBundle\Entity\Page as Page;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity
 */
class PageLink extends Page
{
    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="Page")
     */
    private $targetPage;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Groups({"detail_page"})
     */
    private $url;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        if (null != $url && 0 !== mb_strpos($url, 'http://') && 0 !== mb_strpos($url, 'https://')) {
            $url = 'http://'.$url;
        }

        $this->url = $url;
        return $this;
    }

    public function setTargetPage($targetPage)
    {
        $this->targetPage = $targetPage;

        return $this;
    }

    public function getTargetPage()
    {
        return $this->targetPage;
    }

    public function getPageType()
    {
        return 'link';
    }

    public function getMetaTitle($locale = null)
    {
        return null;
    }

    public function getMetaDescription($locale = null)
    {
        return null;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    public function getTargetPageId()
    {
        return $this->getTargetPage() ? $this->getTargetPage()->getId() : null;
    }
}
