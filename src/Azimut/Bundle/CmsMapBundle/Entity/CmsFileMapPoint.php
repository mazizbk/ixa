<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-21 14:46:25
 */

namespace Azimut\Bundle\CmsMapBundle\Entity;

use Azimut\Bundle\FormExtraBundle\Model\Geolocation;
use Azimut\Bundle\FormExtraBundle\Model\MapPointPosition;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileCommentTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_map_point")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="map_point")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 */
class CmsFileMapPoint extends CmsFile
{
    use CmsFileCommentTrait {
        CmsFileCommentTrait::__construct as private __constructCmsFileCommentTrait;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileCommentTrait();
    }

    protected static $allowPublicApi = true;

    /**
     * @var AccessRightCmsFileMapPoint
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileMapPoint", mappedBy="cmsfilemap_point")
     */
    protected $accessRights;

    /**
     * @var Geolocation
     *
     * @ORM\Column(type="object", nullable=false)
     * @Groups({"detail_cms_file", "public_list_cms_file", "public_detail_cms_file"})
     */
    protected $geolocation;

    /**
     * @var MapPointPosition
     *
     * @ORM\Column(type="object", nullable=false)
     * @Groups({"detail_cms_file", "public_list_cms_file", "public_detail_cms_file"})
     */
    protected $position;

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public static function getCmsFileType()
    {
        return 'map_point';
    }

    public function getGeolocation()
    {
        return $this->geolocation;
    }

    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file", "public_list_cms_file", "public_detail_cms_file"})
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileMapPointTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileMapPointTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file", "public_list_cms_file", "public_detail_cms_file"})
     */
    public function getText($locale = null)
    {
        /** @var CmsFileMapPointTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    public function getPlainText($locale = null)
    {
        $text = $this->getText($locale);
        if (is_array($text)) {
            foreach ($text as $locale => $translatedText) {
                $text[$locale] = strip_tags(html_entity_decode($translatedText));
            }
            return $text;
        }
        return strip_tags(html_entity_decode($text));
    }

    public function getAbstract($locale = null)
    {
        $text = $this->getPlainText($locale);

        return $text;
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileMapPointTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }
}
