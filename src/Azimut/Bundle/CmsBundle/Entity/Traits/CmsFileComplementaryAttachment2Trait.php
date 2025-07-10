<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 10:22:16
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

trait CmsFileComplementaryAttachment2Trait
{
    /**
     * @var CmsFileMediaDeclinationAttachment
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment", cascade={"remove","persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"detail_cms_file","public_detail_cms_file"})
     */
    protected $complementaryAttachment2;

    public static function getComplementaryAttachment2Label()
    {
        return 'complementary.attachment.2';
    }

    public function getComplementaryAttachment2()
    {
        return $this->complementaryAttachment2;
    }

    public function setComplementaryAttachment2($attachment)
    {
        $this->complementaryAttachment2 = $attachment;
        if (null != $attachment && $attachment->getCmsFile() != $this) {
            $attachment->setCmsFile($this);
        }
        return $this;
    }
}
