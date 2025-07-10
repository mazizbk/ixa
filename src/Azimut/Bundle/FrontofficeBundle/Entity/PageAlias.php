<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2013-09-13
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\FrontofficeBundle\Entity\Page as Page;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity
 */
class PageAlias extends Page
{
    /**
     * @var PageContent
     *
     * @ORM\ManyToOne(targetEntity="PageContent")
     * @ORM\JoinColumn(name="page_content_id", referencedColumnName="id")
     */
    private $pageContent;

    public function setPageContent($pageContent)
    {
        $this->pageContent = $pageContent;

        return $this;
    }

    public function getPageContent()
    {
        return $this->pageContent;
    }

    /*public function getTitle()
    {
        if(null != parent::getTitle()) return parent::getTitle();

        return (null != $this->getPageContent()) ? $this->getPageContent()->getTitle() : null;
    }

    public function getSlug()
    {
        if(null != parent::getSlug()) return parent::getSlug();

        return (null != $this->getPageContent()) ? $this->getPageContent()->getSlug() : null;
    }*/

    /**
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    public function getPageContentId()
    {
        return $this->getPageContent() ? $this->getPageContent()->getId() : null;
    }

    public function getPageType()
    {
        return 'alias';
    }

    public function getTemplate()
    {
        return $this->getPageContent() ? $this->getPageContent()->getTemplate() : null;
    }

    public function getTemplateOptions()
    {
        return $this->getPageContent() ? $this->getPageContent()->getTemplateOptions() : null;
    }

    public function getZone($name)
    {
        return $this->getPageContent() ? $this->getPageContent()->getZone($name) : null;
    }

    public function getZones()
    {
        return $this->getPageContent() ? $this->getPageContent()->getZones() : null;
    }
}
