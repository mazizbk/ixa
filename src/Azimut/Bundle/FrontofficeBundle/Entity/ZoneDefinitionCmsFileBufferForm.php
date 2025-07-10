<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-05 15:23:51
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity
 */
class ZoneDefinitionCmsFileBufferForm extends ZoneDefinition
{
    const ZONE_DEFINITION_TYPE = 'cms_file_buffer_form';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail_page_layout"})
     */
    private $cmsFileBufferClass;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="Zone")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $targetZone;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Groups({"detail_page_layout"})
     */
    private $cmsFilePathPriority = 0;

    public function __construct($name = null, $options = null)
    {
        parent::__construct($name, $options);

        if (isset($options['cms_file_buffer_class'])) {
            $this->setCmsFileBufferClass($options['cms_file_buffer_class']);
        }
    }

    public function getCmsFileBufferClass()
    {
        return $this->cmsFileBufferClass;
    }

    public function setCmsFileBufferClass($cmsFileBufferClass)
    {
        $this->cmsFileBufferClass = $cmsFileBufferClass;
        return $this;
    }

    public function getTargetZone()
    {
        return $this->targetZone;
    }

    public function setTargetZone(Zone $targetZone)
    {
        $zoneDefinition = $targetZone->getZoneDefinition();
        if (!($zoneDefinition instanceof ZoneDefinitionCmsFiles)) {
            throw new \InvalidArgumentException(sprintf('Only zone containing CmsFiles can be targeted. Expecting zone definition of class ZoneDefinitionCmsFiles, "%s" given', get_class(
                $zoneDefinition
            )));
        }

        if ($zoneDefinition->isAutoFillAttachments()) {
            throw new \InvalidArgumentException("Can't target an auto-filled zone");
        }

        /** @var CmsFileBuffer $className */
        $className = $this->cmsFileBufferClass;
        if (!$zoneDefinition->getAcceptedAttachmentClasses()->contains($className::getTargetCmsFileClass())) {
            throw new \InvalidArgumentException(sprintf('Target zone do not accept CmsFiles of class "%s". Supports: %s', $className::getTargetCmsFileClass(), implode(',', $zoneDefinition->getAcceptedAttachmentClasses()->toArray())) );
        }

        $this->targetZone = $targetZone;
        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page_layout"})
     */
    public function getTargetZoneId()
    {
        return $this->getTargetZone() ? $this->getTargetZone()->getId() : null;
    }

    /**
     * @return mixed
     */
    public function getCmsFilePathPriority()
    {
        return $this->cmsFilePathPriority;
    }

    public function setCmsFilePathPriority($cmsFilePathPriority)
    {
        $this->cmsFilePathPriority = $cmsFilePathPriority;
        return $this;
    }
}
