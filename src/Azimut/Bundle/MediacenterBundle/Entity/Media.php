<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaRepository;
use Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaDeclinationRepository;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaRepository")
 * @ORM\Table(name="mediacenter_media")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class Media implements TranslatableEntityInterface
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;

    protected static $mimeTypes = [];

    const FILE_TYPE_HINT_MESSAGE = '';

    protected static $embedUrls = [];

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list_folders","detail_folder","list_medias","detail_media","detail_mediaDeclination","list_mediaDeclinations","list_trash_bin","security_access_right_obj"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ\._\s\-'’()]+$/i",
     *     message="this.value.must.not.contain.special.characters"
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "media.name.cannot.be.longer.than.%limit%.characters"
     * )
     *
     * @Groups({"list_folders","detail_folder","list_medias","detail_media","detail_mediaDeclination","list_mediaDeclinations","detail_media_declination","list_trash_bin","detail_media_declination_attachments"})
     */
    private $name;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="medias", cascade={"persist"})
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     *
     * @Groups({"detail_media"})
     */
    protected $folder;

    /**
     * @var MediaTranslation[]|ArrayCollection<MediaTranslation>
     *
     * @ORM\OneToMany(targetEntity="MediaTranslation", mappedBy="media", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Groups({"list_folders","detail_folder","list_medias","detail_media"})
     */
    protected $creationDate;

    /**
     * @var MediaDeclination[]|ArrayCollection<MediaDeclination>
     *
     * @ORM\OneToMany(targetEntity="MediaDeclination", mappedBy="media", cascade={"remove","persist"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     * @Groups({"list_folders","detail_folder","list_medias","detail_media"})
     */
    protected $declinations;

    /**
     * @var MediaDeclination
     *
     * @ORM\OneToOne(targetEntity="MediaDeclination")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"list_folders","detail_folder","list_medias","detail_media"})
     */
    protected $mainDeclination;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $trashed = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list_trash_bin"})
     */
    private $trashedFolderPath;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"list_trash_bin"})
     */
    protected $trashedDate;

    /**
     * @var AccessRightMedia[]|ArrayCollection<AccessRightMedia>
     *
     * @ORM\OneToMany(targetEntity="AccessRightMedia", mappedBy="media")
     */
    protected $accessRights;

    public static function getMimeTypes()
    {
        return static::$mimeTypes;
    }

    public static function getEmbedUrls()
    {
        return static::$embedUrls;
    }

    public static function createFromFile(File $file, MediaRepository $mediaRepository, MediaDeclinationRepository $mediaDeclinationRepository)
    {
        $mimeType = $file->getMimeType();

        // accept mp3 files with wrong mime type
        if ($file instanceof UploadedFile && 'application/octet-stream' == $mimeType && 'audio/mp3' == $file->getClientMimeType()) {
            $mimeType = 'audio/mpeg';
        }

        $media = $mediaRepository->createInstanceFromMimeType($mimeType);

        if (null === $media) {
            return null;
        }

        $mediaDeclination = $mediaDeclinationRepository->createInstanceFromString($media::getMediaType());

        $mediaDeclination->setFile($file);
        $mediaDeclination->setMedia($media);

        $media->setRequiredFieldsFromFile($file);

        return $media;
    }

    public static function createFromEmbedHtml($embedHtml, MediaRepository $mediaRepository, MediaDeclinationRepository $mediaDeclinationRepository)
    {
        $media = $mediaRepository->createInstanceFromEmbedHtml($embedHtml);

        if (null === $media) {
            return null;
        }

        $mediaDeclination = $mediaDeclinationRepository->createInstanceFromString($media::getMediaType());

        // extract iframe tag only
        if (1 == preg_match('/<iframe[^>]+>[^<]*<\/iframe>/', $embedHtml, $match)) {
            $iframe = $match[0];
        } else {
            $iframe = null;
        }

        $mediaDeclination->setEmbedHtml($iframe);
        $mediaDeclination->setMedia($media);

        $media->setRequiredFieldsFromEmbedHtml($embedHtml);

        return $media;
    }

    public function __construct()
    {
        $this->declinations = new ArrayCollection();
        //$this->cmsFilesAttached = new ArrayCollection();
        $this->translations = new ArrayCollection();

        $this->creationDate = new \DateTime();
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function getFormType()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\', '\\Form\\Type\\', $type);
        $type.= 'Type';

        return $type;
    }

    public function getDeclinationClass()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\Media', '\\Entity\\MediaDeclination', $type);

        return $type;
    }

    public function setRequiredFieldsFromFile(File $file)
    {
        if ($file instanceof UploadedFile) {
            $declinationName = $file->getClientOriginalName();
        }
        else {
            $declinationName = $file->getFilename();
        }

        $declinationName = $this->cleanMediaDeclinationName($declinationName);

        $this->setName($declinationName);
        $this->getMainDeclination()->setName($declinationName);

        return $this;
    }

    public function setRequiredFieldsFromEmbedHtml($embedHtml)
    {
        // default name
        $declinationName = 'Embed media';

        // extract name from first <a> tag found in HTML
        if (1 == preg_match('/<a[^>]+>(.+?)<\/a>/', $embedHtml, $match)) {
            $declinationName = $match[1];
        }

        $declinationName = $this->cleanMediaDeclinationName($declinationName);

        $this->setName($declinationName);
        $this->getMainDeclination()->setName($declinationName);

        return $this;
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        if ($this->declinations->count() == 1) {
            $this->declinations[0]->setName($name);
        }

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_media"})
     */
    public function getDescription($locale = null)
    {
        /** @var MediaTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getDescription();
    }

    public function setDescription($description, $locale = null)
    {
        /** @var MediaTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setDescription($description);

        return $this;
    }

    /**
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    public function setFolder($folder)
    {
        if ($this->folder != $folder) {
            if (null != $this->folder) {
                $this->folder->addSize(-$this->getSize());
            }

            $this->folder = $folder;

            if (null != $folder) {
                $folder->addSize($this->getSize());
            }

            //$folder->addMedia($this);
        }

        return $this;
    }

    public function addMediaDeclination(MediaDeclination $mediaDeclination)
    {
        if (!$this->declinations->contains($mediaDeclination)) {
            $this->declinations->add($mediaDeclination);

            $mediaDeclination->setMedia($this);
            if (!$this->mainDeclination) {
                $this->setMainDeclination($mediaDeclination);
            }
        }

        return $this;
    }

    public function removeMediaDeclination($mediaDeclination)
    {
        if ($this->declinations->contains($mediaDeclination)) {
            //TODO: if is was the main declination, set main to another one
            $this->declinations->remove($mediaDeclination);
        }

        return $this;
    }

    public function hasMediaDeclinations()
    {
        return count($this->declinations) > 0;
    }

    public function getMediaDeclinations()
    {
        return $this->declinations;
    }

    public function setMediaDeclinations(ArrayCollection $declinations)
    {
        foreach ($declinations as $declination) {
            $declination->setMedia($this);
        }

        $this->declinations = $declinations;
    }

    public function setMainDeclination($mediaDeclination)
    {
        $this->mainDeclination = $mediaDeclination;
        //$this->setThumb($mediaDeclination->getThumb());
        return $this;
    }

    public function getMainDeclination()
    {
        return $this->mainDeclination;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_folders","detail_folder","list_medias","detail_media"})
     */
    public function getPath()
    {
        return $this->getMainDeclination() ? $this->getMainDeclination()->getPath() : null;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_folders","detail_folder","list_medias","detail_media","list_trash_bin"})
     */
    //TODO : is there a way to automate this by returning the discrimator column "type" ?
    public static function getMediaType()
    {
        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_media", "detail_media_declination"})
     */
    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-file';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_medias"})
     */
    public function getFolderId()
    {
        if (null === $this->getFolder()) {
            return null;
        }

        return $this->getFolder()->getId();
    }

    public function getSize()
    {
        $size = 0;
        foreach ($this->getMediaDeclinations() as $mediaDeclination) {
            $size += $mediaDeclination->getSize();
        }

        return $size;
    }

    public function addSize($delta)
    {
        if (null != $this->folder) {
            $this->folder->addSize($delta);
        }
    }

    /**
     * Set trashed
     *
     * @param  boolean $trashed
     * @return Media
     */
    public function setTrashed($trashed, $unlinkFromParent = true)
    {
        if (null === $trashed) {
            $trashed = false;
        }
        $this->trashed = $trashed;

        if ($trashed) {
            if (true === $unlinkFromParent) {
                if ($folder = $this->getFolder()) {
                    $folder->removeMedia($this);
                }
                $this->setFolder(null);
                $this->setTrashedFolderPath($folder->getFullName());
            }
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

    public function getTrashedFolderPath()
    {
        return $this->trashedFolderPath;
    }

    public function setTrashedFolderPath($trashedFolderPath)
    {
        $this->trashedFolderPath = $trashedFolderPath;

        return $this;
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

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public static function getAccessRightClassName()
    {
        //can't do it dynamically coz of object that extend media should have the same access right
        return AccessRightMedia::class;
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'media';
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Folder::class;
    }

    public function getParentsSecurityContextObject()
    {
        return null == $this->getFolder()?:[$this->getFolder()];
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        return [];
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [];
    }

    /**
     * @Assert\IsTrue(message="a.media.must.belong.to.a.folder.or.to.trashbin")
     */
    public function isValidNullFolder()
    {
        if (null === $this->folder && false === $this->trashed) {
            return false;
        }
        return true;
    }

    private function cleanMediaDeclinationName($declinationName)
    {
        // Replace some caracters with space
        $declinationName = str_replace(array('_','-'), ' ', $declinationName);
        // Strip extension
        $declinationName = preg_replace("/\\.[^.\\s]{3,4}$/u", "", $declinationName);

        // MacOSX uses normalization form D (NFD) to encode UTF-8, while most other systems use NFC
        if (!\Normalizer::isNormalized($declinationName)) {
            $declinationName = \Normalizer::normalize($declinationName);
        }

        // Strip unwanted special chars
        $declinationName = preg_replace("/[^a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ_\.\s'’]/u", '', $declinationName);

        if (mb_strlen($declinationName) > 100) {
            $declinationName = mb_substr($declinationName, 0, 100);
        }

        return $declinationName;
    }
}
