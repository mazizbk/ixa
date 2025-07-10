<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_title_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="title")
 */
class CmsFileTitleTranslation extends CmsFileTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = ucfirst($title);

        return $this;
    }
}
