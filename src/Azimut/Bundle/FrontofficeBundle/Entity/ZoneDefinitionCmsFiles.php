<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-28 11:15:10
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\ZoneDefinitionCmsFilesRepository")
 */
class ZoneDefinitionCmsFiles extends ZoneDefinition
{
    const ZONE_DEFINITION_TYPE = 'cms_files';

    /**
     * @var string[]
     *
     * @ORM\Column(type="array")
     * @Groups({"detail_page_layout"})
     */
    private $acceptedAttachmentClasses;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
     * @Groups({"detail_page_layout"})
     */
    private $maxAttachmentsCount;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_delete", type="boolean")
     * @Groups({"detail_page_layout"})
     */
    private $allowDeleteAttachments = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page_layout"})
     */
    private $autoFillAttachments = false;

    /**
     * @var ZoneFilter[]|ArrayCollection<ZoneFilter>
     *
     * @ORM\OneToMany(targetEntity="ZoneFilter", mappedBy="zoneDefinition", cascade={"remove","persist"}, orphanRemoval=true)
     * @Groups({"detail_page_layout"})
     */
    private $filters;

    /**
     * @var ZonePermanentFilter[]|ArrayCollection<ZonePermanentFilter>
     *
     * @ORM\OneToMany(targetEntity="ZonePermanentFilter", mappedBy="zoneDefinition", cascade={"remove","persist"}, orphanRemoval=true)
     * @Groups({"detail_page_layout"})
     */
    private $permanentFilters;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Groups({"detail_page_layout"})
     */
    private $cmsFilePathPriority = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page_layout"})
     */
    public $useCanonicalCmsFilePath = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="exclude_untranslated_cms_files", type="boolean")
     * @Groups({"detail_page_layout"})
     */
    protected $excludeUntranslatedCmsFiles = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page_layout"})
     */
    private $hasStandaloneCmsfilesRoutes = false;

    public function __construct($name = null, $options = null)
    {
        parent::__construct($name, $options);

        $this->acceptedAttachmentClasses = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->permanentFilters = new ArrayCollection();

        if (isset($options['accepted_attachment_classes'])) {
            $this->setAcceptedAttachmentClasses($options['accepted_attachment_classes']);
        }
        if (isset($options['max_attachments_count'])) {
            $this->setMaxAttachmentsCount($options['max_attachments_count']);
        }

        if (isset($options['allow_delete'])) {
            $this->setAllowDeleteAttachments($options['allow_delete']);
        }

        if (isset($options['auto_fill_attachments'])) {
            $this->setAutoFillAttachments($options['auto_fill_attachments']);
        }

        if (isset($options['filters'])) {
            $this->setFilters($options['filters']);
        }

        if (isset($options['permanent_filters'])) {
            $this->setPermanentFilters($options['permanent_filters']);
        }

        if (isset($options['cms_file_path_priority'])) {
            $this->setCmsFilePathPriority($options['cms_file_path_priority']);
        }

        if (isset($options['use_canonical_cms_file_path'])) {
            $this->useCanonicalCmsFilePath = $options['use_canonical_cms_file_path'];
        }

        if (isset($options['exclude_untranslated_cms_files'])) {
            $this->excludeUntranslatedCmsFiles = $options['exclude_untranslated_cms_files'];
        }

        if (isset($options['standalone_cmsfiles_routes'])) {
            $this->hasStandaloneCmsfilesRoutes = $options['standalone_cmsfiles_routes'];
        }
    }

    public function getAcceptedAttachmentClasses()
    {
        return $this->acceptedAttachmentClasses;
    }

    public function setAcceptedAttachmentClasses($acceptedAttachmentClasses)
    {
        // force full refresh of collection
        if ($acceptedAttachmentClasses instanceof ArrayCollection) {
            $acceptedAttachmentClasses = $acceptedAttachmentClasses->toArray();
        }
        $this->acceptedAttachmentClasses = new ArrayCollection($acceptedAttachmentClasses);

        return $this;
    }

    public function getMaxAttachmentsCount()
    {
        return $this->maxAttachmentsCount;
    }

    public function setMaxAttachmentsCount($maxAttachmentsCount)
    {
        $this->maxAttachmentsCount = $maxAttachmentsCount;
        return $this;
    }

    public function isAllowDeleteAttachments()
    {
        return $this->allowDeleteAttachments;
    }

    public function setAllowDeleteAttachments($allowDeleteAttachments)
    {
        $this->allowDeleteAttachments = $allowDeleteAttachments;
        return $this;
    }

    public function getAcceptedAttachmentTypes()
    {
        $typeNames = [];
        foreach ($this->acceptedAttachmentClasses as $attachmentClass) {
            $typeNames[] = $attachmentClass::getCmsFileType();
        }
        return $typeNames;
    }

    public function isAutoFillAttachments()
    {
        return $this->autoFillAttachments;
    }

    public function setAutoFillAttachments($autoFillAttachments)
    {
        $this->autoFillAttachments = $autoFillAttachments;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    public function addFilter(ZoneFilter $filter)
    {
        $filter->setZoneDefinition($this);

        if (!$this->filters->contains($filter)) {
            $this->filters->add($filter);
        }

        return $this;
    }

    public function removeFilter($filter)
    {
        if ($this->filters->contains($filter)) {
            $this->filters->removeElement($filter);
        }

        return $this;
    }

    public function hasFilters()
    {
        return $this->filters->count() > 0;
    }

    public function getPermanentFilters()
    {
        return $this->permanentFilters;
    }

    public function setPermanentFilters($permanentFilters)
    {
        foreach ($permanentFilters as $permanentFilter) {
            $this->addPermanentFilter($permanentFilter);
        }

        return $this;
    }

    public function addPermanentFilter(ZonePermanentFilter $permanentFilter)
    {
        $permanentFilter->setZoneDefinition($this);

        if (!$this->permanentFilters->contains($permanentFilter)) {
            $this->permanentFilters->add($permanentFilter);
        }

        return $this;
    }

    public function removePermanentFilter($permanentFilter)
    {
        if ($this->permanentFilters->contains($permanentFilter)) {
            $this->permanentFilters->removeElement($permanentFilter);
        }

        return $this;
    }

    public function hasPermanentFilters()
    {
        return $this->permanentFilters->count() > 0;
    }

    public function getCmsFilePathPriority()
    {
        return $this->cmsFilePathPriority;
    }

    public function setCmsFilePathPriority($cmsFilePathPriority)
    {
        $this->cmsFilePathPriority = $cmsFilePathPriority;
        return $this;
    }

    /**
     * Get or set excludeUntranslatedCmsFiles
     *
     * @return bool
     */
    public function excludeUntranslatedCmsFiles($excludeUntranslatedCmsFiles = null)
    {
        if (null !== $excludeUntranslatedCmsFiles) {
            $this->excludeUntranslatedCmsFiles = $excludeUntranslatedCmsFiles;
            return $this;
        }

        return $this->excludeUntranslatedCmsFiles;
    }

    public function hasStandaloneCmsfilesRoutes()
    {
        return $this->hasStandaloneCmsfilesRoutes;
    }

    public function setHasStandaloneCmsfilesRoutes($hasStandaloneCmsfilesRoutes)
    {
        $this->hasStandaloneCmsfilesRoutes = $hasStandaloneCmsfilesRoutes;
        return $this;
    }
}
