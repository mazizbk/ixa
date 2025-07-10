<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-09-13
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\FrontofficeBundle\Entity\Repository\PageContentRepository;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\PageContentRepository")
 */
class PageContent extends Page
{
    /**
     * @var Zone[]|ArrayCollection<Zone>
     *
     * @ORM\OneToMany(targetEntity="Zone", mappedBy="pageContent", cascade={"remove","persist"}, orphanRemoval=true)
     * @Groups({"detail_page"})
     */
    private $zones;

    /**
     * @var PageLayout
     *
     * @ORM\ManyToOne(targetEntity="PageLayout")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     * @Groups({"detail_page"})
     * @Assert\NotNull()
     */
    private $layout;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var string
     */
    private $status;

    public function __construct()
    {
        parent::__construct();
        $this->status = PageContentRepository::STATUS_DRAFT;
        $this->zones = new ArrayCollection();
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime $publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set public
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Check if the page is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->status == PageContentRepository::STATUS_PUBLISH;
    }

    /**
     * Set the status and the publication date
     * of the page.
     */
    public function publish()
    {
        $this->setStatus(PageContentRepository::STATUS_PUBLISH);
        $this->setPublishedAt(new \DateTime());
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return PageLayout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout(PageLayout $layout)
    {
        if (null != $this->layout && $this->layout !== $layout) {
            //delete zones of old layout
            $this->zones->clear();
        }

        $this->layout = $layout;

        $zoneDefinitions = $this->layout->getZoneDefinitions();

        foreach ($zoneDefinitions as $zoneDefinition) {
            $zone = new Zone([
                'page_content' => $this,
                'zone_definition' => $zoneDefinition
            ]);
            //$zone->setPageContent($this);
            //$zone->setZoneDefinition($zoneDefinition);
            $this->zones->add($zone);
        }

        return $this;
    }

    public function getTemplate()
    {
        if (null === $this->layout) {
            throw new \RuntimeException('Layout template not set in page.');
        }

        return $this->layout->getTemplate();
    }

    public function getTemplateOptions()
    {
        if (null === $this->layout) {
            throw new \RuntimeException('Layout template not set in page.');
        }

        return $this->layout->getTemplateOptions();
    }

    public function getZone($name)
    {
        if (null === $this->layout) {
            throw new \RuntimeException('Cannot get zone: no layout set in current page.');
        }

        foreach ($this->zones as $zone) {
            if ($zone->getName() == $name) {
                return $zone;
            }
        }

        $zoneDefinition = $this->layout->getZoneDefinition($name);

        $zone = new Zone();
        $zone->setPageContent($this);
        $zone->setZoneDefinition($zoneDefinition);
        $this->zones->add($zone);

        return $zone;
    }

    public function getZones()
    {
        return $this->zones;
    }

    public function addZone(Zone $zone)
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
            $zone->setPageContent($this);
        }

        return $this;
    }

    public function removeZone($zone)
    {
        if ($this->zones->contains($zone)) {
            $this->zones->removeElement($zone);
        }

        return $this;
    }

    public function getPageType()
    {
        return 'content';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_menu"})
     */
    public function isFullPageCmsFile()
    {
        return $this->layout->isFullPageCmsFile();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_menu"})
     */
    public function fullPageCmsFileId()
    {
        if (true === $this->layout->isFullPageCmsFile()) {
            return $this->zones[0]->getAttachments()[0]->getCmsFile()->getId();
        }
        return null;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_menu"})
     */
    public function isMonoZone()
    {
        return (1 == $this->zones->count());
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_menu"})
     */
    public function monoZoneId()
    {
        if (true === $this->isMonoZone()) {
            return $this->zones[0]->getId();
        }
        return null;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page"})
     */
    public function isChildPageAllowed()
    {
        if (!parent::isChildPageAllowed()) {
            return false;
        }

        return $this->layout->isChildPageAllowed();
    }

    /**
     * Does this page contain a zone exposing standalone cmsfiles routes
     *
     * @return boolean
     */
    public function hasZoneHavingStandaloneCmsfilesRoutes()
    {
        foreach ($this->zones as $zone) {
            if ($zone->getZoneDefinition() instanceof ZoneDefinitionCmsFiles && $zone->getZoneDefinition()->hasStandaloneCmsfilesRoutes()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    /*public function getLayoutId() {
        return $this->getLayout()?$this->getLayout()->getId():null;
    }*/

    /** TO DO

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
        // ...

    }*/
}
