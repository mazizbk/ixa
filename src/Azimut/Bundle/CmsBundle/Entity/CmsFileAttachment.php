<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-01 14:27:05
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_attachment")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class CmsFileAttachment
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_attached_cms_file"})
     */
    protected $id;

    /**
     * @var CmsFile
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFile", inversedBy="attachments", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @Groups({"detail_attached_cms_file"})
     */
    protected $cmsFile;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"detail_attached_cms_file"})
     */
    protected $displayOrder = 1;

    public function __construct($cmsFile = null)
    {
        if (null != $cmsFile) {
            $this->setCmsFile($cmsFile);
        }
    }

    public function __toString()
    {
        return $this->getCmsFile()->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CmsFile $cmsFile
     * @return $this
     */
    public function setCmsFile(CmsFile $cmsFile)
    {
        $this->cmsFile = $cmsFile;
        //$cmsFile->addAttachedObject // TODO : how could we implement the revert side ? DQL ?
        return $this;
    }

    /**
     * @return CmsFile
     */
    public function getCmsFile()
    {
        return $this->cmsFile;
    }

    /**
     * @param $displayOrder
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }
}
