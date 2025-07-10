<?php
/**
 * Created by mikaelp on 2018-10-17 5:25 PM
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_title_and_text_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="title_and_text")
 */
class CmsFileTitleAndTextTranslation extends CmsFileTranslation
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

}
