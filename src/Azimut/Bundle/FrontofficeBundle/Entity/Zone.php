<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-28 15:14:21
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\ZoneRepository")
 * @ORM\Table(name="frontoffice_zone")
 */
class Zone implements TranslatableEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_page","detail_zone"})
     */
    protected $id;

    /**
     * @var ZoneTranslation[]|ArrayCollection<ZoneTranslation>
     *
     * @ORM\OneToMany(targetEntity="ZoneTranslation", mappedBy="zone", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var PageContent|null
     *
     * @ORM\ManyToOne(targetEntity="PageContent", inversedBy="zones")
     * @ORM\JoinColumn(name="page_content_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    protected $pageContent;

    /**
     * @var ZoneDefinition
     *
     * @ORM\ManyToOne(targetEntity="ZoneDefinition")
     * @ORM\JoinColumn(name="zone_definition_id", onDelete="cascade")
     */
    private $zoneDefinition;

    /**
     * @var ZoneCmsFileAttachment[]|ArrayCollection<ZoneCmsFileAttachment>
     *
     * @ORM\OneToMany(targetEntity="ZoneCmsFileAttachment", mappedBy="zone", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     * @Groups({"detail_zone"})
     */
    protected $attachments;

    public function __construct($params = [])
    {
        $this->translations = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        if (isset($params['page_content'])) {
            $this->setPageContent($params['page_content']);
        }

        if (isset($params['zone_definition'])) {
            $this->setZoneDefinition($params['zone_definition']);
        }
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    static function getTranslationClass()
    {
        return static::class.'Translation';
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_zone"})
     * @param string|null $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var ZoneTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var ZoneTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page","detail_zone"})
     */
    public function getName()
    {
        if (null === $this->zoneDefinition) {
            throw new \RuntimeException('Cannot return Zone name: no definition set on entity.');
        }

        return $this->zoneDefinition->getName();
    }

    public function setPageContent($pageContent)
    {
        if ($this->pageContent != $pageContent) {
            $this->pageContent = $pageContent;
            //$pageContent->addZone($this);
        }

        return $this;
    }

    /**
     * @return PageContent
     */
    public function getPageContent()
    {
        return $this->pageContent;
    }

    public function getPage()
    {
        return $this->getPageContent();
    }

    /**
     * @return ZoneDefinition
     */
    public function getZoneDefinition()
    {
        return $this->zoneDefinition;
    }

    public function setZoneDefinition(ZoneDefinition $zoneDefinition)
    {
        $this->zoneDefinition = $zoneDefinition;

        // if zone content cannot be deleted, create a CMSFile inside the zone
        if ($zoneDefinition instanceof ZoneDefinitionCmsFiles && false == $zoneDefinition->isAllowDeleteAttachments() && $zoneDefinition->getAcceptedAttachmentClasses()->count() > 0) {
            $cmsFileAttachedClass = $zoneDefinition->getAcceptedAttachmentClasses()[0];

            for ($i=0; $i < $zoneDefinition->getMaxAttachmentsCount(); $i++) {
                $cmsFile = new $cmsFileAttachedClass;
                $attachment = new ZoneCmsFileAttachment();
                $attachment
                    ->setZone($this)
                    ->setCmsFile($cmsFile)
                ;
                $this->addAttachment($attachment);
            }
        }

        return $this;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function addAttachment($attachment)
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);

            //$attachment->setZone($this);
        }

        return $this;
    }

    public function hasAttachments()
    {
        return count($this->attachments) > 0;
    }

    public function removeAttachment($attachment)
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->remove($attachment);
        }

        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_zone"})
     */
    public function getAcceptedAttachmentTypes()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        if (null === $this->zoneDefinition) {
            throw new \RuntimeException('Cannot return Zone accepted attachment types: no definition set on entity.');
        }

        return $this->zoneDefinition->getAcceptedAttachmentTypes();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_zone"})
     */
    public function getPageId()
    {
        return $this->getPage()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_zone"})
     */
    public function getMaxAttachmentsCount()
    {
        if (null === $this->zoneDefinition) {
            throw new \RuntimeException("Cannot return Zone's max attachments count: no definition set on entity.");
        }

        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        return $this->zoneDefinition->getMaxAttachmentsCount();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_zone"})
     */
    public function isAllowDeleteAttachments()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        return $this->zoneDefinition->isAllowDeleteAttachments();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page", "detail_zone"})
     */
    public function isFullZoneCmsFile()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return false;
        }

        return (!$this->isAllowDeleteAttachments() && 1 == $this->attachments->count() && 1 == $this->getMaxAttachmentsCount());
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page", "detail_zone"})
     */
    public function fullZoneCmsFileId()
    {
        if (true === $this->isFullZoneCmsFile()) {
            return $this->getAttachments()[0]->getCmsFile()->getId();
        }
        return null;
    }

    public function getFilters()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        return $this->zoneDefinition->getFilters();
    }

    public function getPermanentFilters()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        return $this->zoneDefinition->getPermanentFilters();
    }

    public function hasFilters()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return false;
        }

        return $this->zoneDefinition->hasFilters();
    }

    public function hasPermanentFilters()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return false;
        }

        return $this->zoneDefinition->hasPermanentFilters();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page", "detail_zone"})
     */
    public function isAutoFillAttachments()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return false;
        }

        return $this->zoneDefinition->isAutoFillAttachments();
    }

    public function getCmsFilePathPriority()
    {
        if (!($this->zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            return null;
        }

        return $this->zoneDefinition->getCmsFilePathPriority();
    }

    /**
     * Get page's full slug
     *
     * @VirtualProperty
     * @Groups({"detail_zone"})
     *
     * @return string
     */
    public function getPageFullSlug()
    {
        return $this->getPage()->getFullSlug();
    }

    /**
     * Get site's URI
     *
     * @VirtualProperty
     * @Groups({"detail_zone"})
     *
     * @return string
     */
    public function getSiteUri()
    {
        return $this->getPage()->getSite()->getUri();
    }

    /**
     * Reindex display orders (fix sequence holes)
     * Warning: this will load all attachments objects
     * @return self
     */
    public function reindexAttachmentsDisplayOrder()
    {
        $attachments = $this->attachments->toArray();
        usort($attachments, function($a, $b) {
            return $a->getDisplayOrder() > $b->getDisplayOrder();
        });

        foreach ($attachments as $key => $attachment) {
            if ($key + 1 != $attachment->getDisplayOrder()) {
                $attachment->setDisplayOrder($key + 1);
            }
        }

        return $this;
    }
}
