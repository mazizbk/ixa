<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 10:22:16
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

trait CmsFileComplementaryAttachment4Trait
{
    /**
     * @var CmsFileMediaDeclinationAttachment
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $complementaryAttachment4;

    public static function getComplementaryAttachment4Label()
    {
        return 'complementary.attachment.4';
    }

    public function getComplementaryAttachment4()
    {
        return $this->complementaryAttachment4;
    }

    public function setComplementaryAttachment4($attachment)
    {
        $this->complementaryAttachment4 = $attachment;
        if (null != $attachment && $attachment->getCmsFile() != $this) {
            $attachment->setCmsFile($this);
        }
        return $this;
    }
}
