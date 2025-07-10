<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-22 10:20:21
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_text")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="text")
 */
class CmsFileText extends CmsFile
{
    /**
     * @var AccessRightCmsFileText[]|ArrayCollection<AccessRightCmsFileText>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileText", mappedBy="cmsfiletext")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getText($locale);
    }

    public static function getCmsFileType()
    {
        return 'text';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getText($locale = null)
    {
        /** @var CmsFileTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }

    public function getAbstract($locale = null)
    {
        return html_entity_decode($this->getText($locale));
    }
}
