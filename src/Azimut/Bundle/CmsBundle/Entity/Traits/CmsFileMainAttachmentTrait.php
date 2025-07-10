<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-11-04 17:00:01
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

trait CmsFileMainAttachmentTrait
{
    /**
     * @var CmsFileMediaDeclinationAttachment
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $mainAttachment;

    public static function getMainAttachmentLabel()
    {
        return 'main.attachment';
    }

    public function getMainAttachment()
    {
        return $this->mainAttachment;
    }

    public function setMainAttachment($attachment)
    {
        $this->mainAttachment = $attachment;
        if (null != $attachment && $attachment->getCmsFile() != $this) {
            $attachment->setCmsFile($this);
        }
        return $this;
    }
}
