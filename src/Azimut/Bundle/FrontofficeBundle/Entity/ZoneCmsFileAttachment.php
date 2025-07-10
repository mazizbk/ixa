<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-28 15:34:16
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\CmsBundle\Entity\CmsFileAttachment;
use Azimut\Bundle\BackofficeBundle\Entity\RaiseEventsInterface;
use Azimut\Bundle\BackofficeBundle\Entity\Traits\RaiseEventsTrait;
use Azimut\Bundle\FrontofficeBundle\Event\Entity\ZoneCmsFileAttachmentRemoved;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\ZoneCmsFileAttachmentRepository")
 * @ORM\Table(name="frontoffice_zone_cmsfile_attachment")
 * @ORM\HasLifecycleCallbacks
 * @DynamicInheritanceSubClass(discriminatorValue="zone")
 */
class ZoneCmsFileAttachment extends CmsFileAttachment implements RaiseEventsInterface
{
    use RaiseEventsTrait;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="Zone", inversedBy="attachments")
     * @ORM\JoinColumn(name="zone_id", referencedColumnName="id", nullable=false, onDelete="cascade")
     */
    protected $zone;

    public function __construct($zone = null, $cmsFile = null)
    {
        if (null != $zone) {
            $this->setZone($zone);
        }
        parent::__construct($cmsFile);
    }

    public function setZone(Zone $zone)
    {
        if (!$zone->getZoneDefinition() instanceof ZoneDefinitionCmsFiles) {
            throw new \InvalidArgumentException(sprintf('Expected zone definition of class "ZoneDefinitionCmsFiles", instance of "%s" given.', get_class($zone->getZoneDefinition())));
        }

        if ($this->zone !== $zone) {
            $this->zone = $zone;
            $zone->addAttachment($this);
        }

        return $this;
    }

    /**
     * @return Zone
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_attached_cms_file"})
     */
    public function getZoneId()
    {
        return $this->getZone()->getId();
    }

    /**
     * @Assert\IsTrue(message = "zone.max.attachments.reached")
     */
    public function isMaxAttachmentsCorrectOnZone()
    {
        $zoneDefinition = $this->zone->getZoneDefinition();

        if(!$zoneDefinition instanceof ZoneDefinitionCmsFiles) {
            throw new \InvalidArgumentException('Zone definition is not a ZoneDefinitionCmsFiles');
        }
        $maxAttachmentsCount = $zoneDefinition->getMaxAttachmentsCount();

        if (null == $maxAttachmentsCount) {
            return true;
        }

        return ($this->zone->getAttachments()->count() <= $maxAttachmentsCount);
    }

    /**
     * @ORM\PreRemove
     */
    public function onRemove()
    {
        $this->raiseEvent(new ZoneCmsFileAttachmentRemoved($this));
    }
}
