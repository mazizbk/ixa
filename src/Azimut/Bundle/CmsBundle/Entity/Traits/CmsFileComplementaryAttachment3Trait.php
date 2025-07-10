<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 10:22:16
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

trait CmsFileComplementaryAttachment3Trait
{
    /**
     * @var CmsFileMediaDeclinationAttachment
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $complementaryAttachment3;

    public static function getComplementaryAttachment3Label()
    {
        return 'complementary.attachment.3';
    }

    public function getComplementaryAttachment3()
    {
        return $this->complementaryAttachment3;
    }

    public function setComplementaryAttachment3($attachment)
    {
        $this->complementaryAttachment3 = $attachment;
        if (null != $attachment && $attachment->getCmsFile() != $this) {
            $attachment->setCmsFile($this);
        }
        return $this;
    }
}
