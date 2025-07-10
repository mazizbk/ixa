<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 10:51:35
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Azimut\Component\PHPExtra\TraitHelper;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileCommentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileProductTrait;
use Azimut\Bundle\BackofficeBundle\Entity\RaiseEventsInterface;
use Azimut\Bundle\BackofficeBundle\Entity\Traits\RaiseEventsTrait;
use Azimut\Bundle\CmsBundle\Event\Entity\CmsFileCreated;
use Azimut\Bundle\CmsBundle\Event\Entity\CmsFileRemoved;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile", indexes={@ORM\Index(name="slug_idx", columns={"slug"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class CmsFile implements TranslatableEntityInterface, RaiseEventsInterface
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;
    use RaiseEventsTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"always", "list_cms_files", "detail_cms_file", "detail_attached_cms_file", "list_trash_bin",  "security_access_right_obj", "public_list_cms_file", "public_detail_cms_file", "list_comments", "detail_comment"})
     */
    protected $id;

    /**
     * @var CmsFileTranslation[]|ArrayCollection<CmsFileTranslation>
     *
     * @ORM\OneToMany(targetEntity="CmsFileTranslation", mappedBy="cmsFile", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $publishStartDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $publishEndDatetime;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $trashed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"list_trash_bin"})
     */
    protected $trashedDate;

    protected static $allowPublicApi = false;

    /**
     * @var MediaDeclination[]|ArrayCollection<MediaDeclination>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinTable(name="cms_cmsfile_embedded_media_declination")
     */
    protected $embeddedMediaDeclinations;
    // Potential improvement: embeddedMediaDeclinations should use CmsFileMediaDeclinationAttachment (or a dedicated class inheriting AbstractMediaDeclinationAttachment) instead of direct link (it would simplify getting all publications from a media)

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $slug;

    // main full slug depends on context (ex: in wich Site it is publish),
    // so it's not mapped and populated in controllers
    protected $canonicalPath;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileAttachment", mappedBy="cmsFile", cascade={"persist", "remove"}, orphanRemoval=true))
     */
    protected $attachments;

    public function __construct()
    {
        $this->id = uniqid();
        $this->raiseEvent(new CmsFileCreated($this));

        $this->attachments = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->embeddedMediaDeclinations = new ArrayCollection();
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        if (null != $this->getName()) {
            return $this->getName();
        }

        foreach ($this->translations as $translation) {
            if (null != $this->getName($translation->getLocale())) {
                return $this->getName($translation->getLocale());
            }
        }

        return (string) $this->getId();
    }

    public function getFormType()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\', '\\Form\\Type\\', $type);
        $type.= 'Type';

        return $type;
    }

    public function hasPublicApi()
    {
        return static::$allowPublicApi;
    }

    public function getId()
    {
        return $this->id;
    }

    static function getTranslationClass()
    {
        return static::class.'Translation';
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Is slug translated
     * @return bool
     */
    public function isSlugTranslated() {
        // By default, translated slug is used if cmsfile has translations
        // If you want to use the untranslated slug for an entity with translations (ex: slugs based on untranslated
        // names), override this method in your CmsFile child class
        return $this->hasTranslations();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files", "detail_cms_file", "detail_attached_cms_file", "list_trash_bin", "list_comments", "detail_comment"})
     * @param string $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files"})
     */
    public function getThumb()
    {
        return '';
    }

    /**
     * @VirtualProperty()
     * @Groups({"list_cms_files"})
     * @param string $locale
     * @return string
     */
    public function getAbstract($locale = null)
    {
        // Overwrite this function in extended classes
        //
        // NB:
        //
        // Do not cut content length here unless necessary, do it in template after stripping
        // media declination tags (because sometimes you'll want to cut at 100 char and sometimes
        // maybe 250, ...)
        //
        // If you choose to cut the content (because it makes no sense to display more than a
        // certain of characters), use mb_substr function instead of substr (because on 2 bytes
        // characters in UTF-8)

        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files","detail_cms_file","detail_attached_cms_file","list_trash_bin"})
     */
    public static function getCmsFileType()
    {
        return '';
    }

    public function getSlug($locale = null)
    {
        if ($this->isSlugTranslated()) {
            /** @var CmsFileTranslation $proxy */
            $proxy = new TranslationProxy($this, $locale);
            return $proxy->getSlug();
        }

        return $this->slug;
    }

    public function setSlug($slug, $locale = null)
    {
        if ($this->isSlugTranslated()) {
            /** @var CmsFileTranslation $proxy */
            $proxy = new TranslationProxy($this, $locale);
            $proxy->setSlug($slug);
        } else {
            $this->slug = $slug;
        }

        return $this;
    }

    public function getCanonicalPath($locale = null)
    {
        if ($this->isSlugTranslated()) {
            /** @var CmsFileTranslation $proxy */
            $proxy = new TranslationProxy($this, $locale);
            return $proxy->getCanonicalPath();
        }

        return $this->canonicalPath;
    }

    public function setCanonicalPath($canonicalPath, $locale = null)
    {
        if ($this->isSlugTranslated()) {
            /** @var CmsFileTranslation $proxy */
            $proxy = new TranslationProxy($this, $locale);
            $proxy->setCanonicalPath($canonicalPath);
        } else {
            $this->canonicalPath = $canonicalPath;
        }

        return $this;
    }

    public function getPublishStartDateTime()
    {
        return $this->publishStartDatetime;
    }

    public function setPublishStartDateTime($publishStartDatetime)
    {
        if (!$publishStartDatetime instanceof \DateTime) {
            $publishStartDatetime = new \DateTime($publishStartDatetime);
        }
        $this->publishStartDatetime = $publishStartDatetime;

        return $this;
    }

    public function getPublishEndDateTime()
    {
        return $this->publishEndDatetime;
    }

    public function setPublishEndDateTime($publishEndDatetime)
    {
        if (null !== $publishEndDatetime && !$publishEndDatetime instanceof \DateTime) {
            $publishEndDatetime = new \DateTime($publishEndDatetime);
        }
        $this->publishEndDatetime = $publishEndDatetime;

        return $this;
    }

    public function isPublished()
    {
        if (!$this->publishStartDatetime) {
            return true;
        }

        $now = new \DateTime();

        if (!$this->publishEndDatetime && $now >= $this->publishStartDatetime) {
            return true;
        }

        if ($now >= $this->publishStartDatetime && $now <= $this->publishEndDatetime) {
            return true;
        }

        return false;
    }

    /**
     * Set trashed
     *
     * @param  boolean $trashed
     * @return $this
     */
    public function setTrashed($trashed)
    {
        if (null === $trashed) {
            $trashed = false;
        }
        $this->trashed = $trashed;

        if ($trashed) {
            $this->setTrashedDate(new \DateTime());
        } else {
            $this->setTrashedDate(null);
        }

        return $this;
    }

    /**
     * Get trashed
     *
     * @return boolean
     */
    public function isTrashed()
    {
        return $this->trashed;
    }

    public function getTrashedDate()
    {
        return $this->trashedDate;
    }

    public function setTrashedDate($trashedDate)
    {
        $this->trashedDate = $trashedDate;

        return $this;
    }

    public function getAccessRightClassName()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflexionClass = new \ReflectionClass($this);
        return $reflexionClass->getNamespaceName().'\AccessRight'.$reflexionClass->getShortName();
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return strtolower('cmsfile'.static::getCmsFileType());
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return null;
    }

    public function getParentsSecurityContextObject()
    {
        return [];
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        if (TraitHelper::isClassUsing(self::class, CmsFileCommentTrait::class)) {
            /** @var CmsFileCommentTrait $obj */
            $obj = $this;
            return $obj->getComments();
        }

        return [];
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [];
    }

    public function getEmbeddedMediaDeclinations()
    {
        return $this->embeddedMediaDeclinations;
    }

    public function setEmbeddedMediaDeclinations(ArrayCollection $mediaDeclinations)
    {
        $this->embeddedMediaDeclinations = $mediaDeclinations;

        return $this;
    }

    public function addEmbeddedMediaDeclination($mediaDeclination)
    {
        if (!$this->embeddedMediaDeclinations->contains($mediaDeclination)) {
            $this->embeddedMediaDeclinations->add($mediaDeclination);
        }

        return $this;
    }

    public function hasEmbeddedMediaDeclinations()
    {
        return count($this->embeddedMediaDeclinations) > 0;
    }

    public function removeEmbeddedMediaDeclination($mediaDeclination)
    {
        if ($this->embeddedMediaDeclinations->contains($mediaDeclination)) {
            $this->embeddedMediaDeclinations->removeElement($mediaDeclination);
        }

        return $this;
    }

    public function hasTranslations()
    {
        return count($this->translations) > 0;
    }

    /**
     * Has translation
     * @param  string  $locale Locale of translation
     * @return boolean
     */
    public function hasTranslation($locale)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() == $locale) {
                return true;
            }
        }
        return false;
    }

    public function isHiddenType()
    {
        // CmsFiles inside FrontofficeBundle namespace are considered hidden
        return 0 === strpos(get_called_class(), 'Azimut\Bundle\FrontofficeBundle');
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files", "detail_cms_file", "detail_attached_cms_file"})
     */
    public function isVisible()
    {
        $now = new \DateTime;
        return
            null == $this->publishStartDatetime && null == $this->publishEndDatetime
            || null == $this->publishEndDatetime && $this->publishStartDatetime <= $now
            || $this->publishStartDatetime <= $now && $this->publishEndDatetime > $now
        ;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_cms_file"})
     */
    public function supportsComments()
    {
        return TraitHelper::isClassUsing(static::class, CmsFileCommentTrait::class);
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_cms_file"})
     */
    public function supportsProductItems()
    {
        return TraitHelper::isClassUsing(static::class, CmsFileProductTrait::class);
    }

    public function getMetaTitle($locale = null)
    {
        return $this->getName($locale);
    }

    public function getMetaDescription($locale = null)
    {
        $metaDescription = $this->getAbstract($locale);
        if (mb_strlen($metaDescription) > 300) {
            $metaDescription = mb_substr($metaDescription, 0, 300) . '…';
        }

        return $metaDescription;
    }

    /**
     * @Assert\IsTrue(message = "publish.end.date.must.be.later.than.start.date")
     */
    public function isValidPublicationDates()
    {
        if (null != $this->publishStartDatetime && null != $this->publishEndDatetime) {
            if (1 == $this->publishStartDatetime->diff($this->publishEndDatetime)->invert) {
                return false;
            }
        }

        return true;
    }

    /**
     * @ORM\PreRemove
     */
    public function onRemove()
    {
        $this->raiseEvent(new CmsFileRemoved($this));
    }
}
