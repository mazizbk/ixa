<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-12-30 16:25:03
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_rich_text_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="rich_text")
 */
class CmsFileRichTextTranslation extends CmsFileTranslation
{
    /**
     * @var string
     *
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
}
