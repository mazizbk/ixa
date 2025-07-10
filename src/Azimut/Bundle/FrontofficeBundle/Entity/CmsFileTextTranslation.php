<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-22 10:21:10
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_text_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="text")
 */
class CmsFileTextTranslation extends CmsFileTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $text;

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = ucfirst($text);

        return $this;
    }
}
