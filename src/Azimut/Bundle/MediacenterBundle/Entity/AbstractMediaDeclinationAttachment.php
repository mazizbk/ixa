<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 13:37:51
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_attachment")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
abstract class AbstractMediaDeclinationAttachment
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_media_declination_attachments","detail_media_declination_attachments", "public_list_media_declination_attachment", "public_detail_media_declination_attachment"})
     */
    protected $id;

    /**
     * @var MediaDeclination
     *
     * @ORM\ManyToOne(targetEntity="MediaDeclination", inversedBy="mediaDeclinationAttachments", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @Groups({"detail_media_declination_attachments", "public_list_media_declination_attachment", "public_detail_media_declination_attachment"})
     */
    protected $mediaDeclination;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media_declination_attachments"})
     */
    protected $cropping;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail_media_declination_attachments"})
     */
    protected $displayOrder;

    public function __construct($mediaDeclination = null)
    {
        if (null === $mediaDeclination) {
            return;
        }

        if (!$mediaDeclination instanceof MediaDeclination) {
            throw new \InvalidArgumentException(sprintf('MediaDeclinationAttachment constructor argument must be an instance of MediaDeclination "%s" given', get_class($mediaDeclination)));
        }

        $this->setMediaDeclination($mediaDeclination);
    }

    public function __toString()
    {
        return $this->getMediaDeclination()->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setMediaDeclination($mediaDeclination)
    {
        if (null != $this->mediaDeclination) {
            $this->mediaDeclination->removeMediaDeclinationAttachment($this);
        }
        $this->mediaDeclination = $mediaDeclination;
        if (null != $this->mediaDeclination) {
            $mediaDeclination->addMediaDeclinationAttachment($this);
    }
        return $this;
    }

    public function setCropping($cropping)
    {
        $this->cropping = $cropping;

        return $this;
    }

    public function getMediaDeclination()
    {
        return $this->mediaDeclination;
    }

    public function getCropping()
    {
        return $this->cropping;
    }

    public function getThumb()
    {
        return $this->mediaDeclination->getThumb();
    }

    /**
     * Get displayOrder
     *
     * @return integer
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Set displayOrder
     *
     * @param integer $displayOrder
     *
     * @return self
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    /**
     * Get object to wich MediaDeclination is attached
     */
    abstract public function getAttachedObject();

    /**
     * Get object's name to wich MediaDeclination is attached
     *
     * @return string|null
     */
    abstract public function getAttachedObjectName();

    /**
     * Get object's type name (literal class name of subtype) to wich MediaDeclination is attached
     *
     * @return string|null
     */
    abstract public function getAttachedObjectTypeName();

    /**
     * Get object's class to wich MediaDeclination is attached
     *
     * @return string|null
     */
    public function getAttachedObjectClass()
    {
        if (null != $this->getAttachedObject()) {
            return get_class($this->getAttachedObject());
        }
        return null;
    }
}
