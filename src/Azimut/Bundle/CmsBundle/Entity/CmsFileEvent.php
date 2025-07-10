<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 16:38:39
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Symfony\Component\Validator\Constraints as Assert;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_event")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="event")
 */
class CmsFileEvent extends CmsFile
{
    use CmsFileMainAttachmentTrait;
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }

    /**
     * @var AccessRightCmsFileEvent[]|ArrayCollection<AccessRightCmsFileEvent>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileEvent", mappedBy="cmsfileevent")
     */
    protected $accessRights;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank()
     * @Groups({"detail_cms_file"})
     */
    protected $eventStartDatetime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $eventEndDatetime;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $location;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
    }

    public static function getCmsFileType()
    {
        return 'event';
    }

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public function getThumb()
    {
        $mainAttachment = $this->getMainAttachment();
        (null != $mainAttachment)?$mainAttachment->getThumb():null;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileEventTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileEventTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getText($locale = null)
    {
        /** @var CmsFileEventTranslation $proxy */
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

        // NB: do not cut content length here, do it in template after stripping media declination tags

        return $text;
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileEventTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }

    public function getEventStartDateTime()
    {
        return $this->eventStartDatetime;
    }

    public function setEventStartDateTime($eventStartDatetime)
    {
        if (!$eventStartDatetime instanceof \DateTime) {
            $eventStartDatetime = new \DateTime($eventStartDatetime);
        }
        $this->eventStartDatetime = $eventStartDatetime;

        return $this;
    }

    public function getEventEndDateTime()
    {
        return $this->eventEndDatetime;
    }

    public function setEventEndDateTime($eventEndDatetime)
    {
        if (null !== $eventEndDatetime && !$eventEndDatetime instanceof \DateTime) {
            $eventEndDatetime = new \DateTime($eventEndDatetime);
        }
        $this->eventEndDatetime = $eventEndDatetime;

        return $this;
    }

    /**
     * Get location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location
     *
     * @param string|null $location
     *
     * @return self
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }
}
