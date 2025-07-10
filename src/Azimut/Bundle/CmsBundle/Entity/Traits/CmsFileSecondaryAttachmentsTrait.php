<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-11-04 12:02:10
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;

trait CmsFileSecondaryAttachmentsTrait
{
    /**
     * Unidirectional One-To-Many
     * @var CmsFileMediaDeclinationAttachment[]|ArrayCollection<CmsFileMediaDeclinationAttachment>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true, onDelete="cascade")})
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $secondaryAttachments;

    public function __construct()
    {
        $this->secondaryAttachments = new ArrayCollection();
    }

    public static function getSecondaryAttachmentsLabel()
    {
        return 'secondary.attachment';
    }

    public function getSecondaryAttachments()
    {
        return $this->secondaryAttachments;
    }

    // public function setSecondaryAttachments(ArrayCollection $attachments)
    // {
    //     $this->secondaryAttachments = $attachments;

    //     return $this;
    // }

    public function addSecondaryAttachment(CmsFileMediaDeclinationAttachment $attachment)
    {
        if (!$this->secondaryAttachments->contains($attachment)) {
            $this->secondaryAttachments->add($attachment);
            $attachment->setCmsFile($this);
        }

        return $this;
    }

    public function hasSecondaryAttachments()
    {
        return count($this->secondaryAttachments) > 0;
    }

    public function removeSecondaryAttachment(CmsFileMediaDeclinationAttachment $attachment)
    {
        if ($this->secondaryAttachments->contains($attachment)) {
            $this->secondaryAttachments->removeElement($attachment);
        }

        return $this;
    }
}
