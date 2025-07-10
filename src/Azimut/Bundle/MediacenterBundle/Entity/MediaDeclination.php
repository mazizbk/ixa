<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-04
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaDeclinationRepository")
 */
abstract class MediaDeclination implements TranslatableEntityInterface
{
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list_media_declinations","detail_media_declination","detail_folder","detail_media","list_folders","list_medias","detail_media_declination_attachments", "public_list_media_declination_attachment", "public_detail_media_declination_attachment"})
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
     *      maxMessage = "media.declination.name.cannot.be.longer.than._limit_.characters"
     * )
     *
     * @Groups({"list_media_declinations","detail_media_declination","detail_media","detail_media_declination_attachments", "public_list_media_declination_attachment", "public_detail_media_declination_attachment"})
     */
    private $name;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Media", inversedBy="declinations")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=false, onDelete="cascade")
     *
     * @Groups({"list_media_declinations","detail_media_declination","detail_media_declination_attachments"})
     */
    protected $media;

    /**
     * @var MediaDeclinationTranslation[]|ArrayCollection<MediaDeclinationTranslation>
     *
     * @ORM\OneToMany(targetEntity="MediaDeclinationTranslation", mappedBy="mediaDeclination", cascade={"persist", "remove"}, indexBy="locale")
     */
    protected $translations;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="2000M")
     * @Assert\NotBlank(groups={"fileRequired"})
     */
    protected $file;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
     */
    protected $size = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     *
     * @Groups({"list_media_declinations","detail_media_declination","detail_media"})
     */
    protected $fileExtension;

    //TODO : change to nullable=false
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail_media_declination","detail_media", "public_list_media_declination_attachment", "public_detail_media_declination_attachment"})
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"list_media_declinations","detail_media_declination","detail_media","detail_media_declination_attachments"})
     */
    protected $thumb;

    /**
     * @var AbstractMediaDeclinationAttachment[]|ArrayCollection<AbstractMediaDeclinationAttachment>
     *
     * @ORM\OneToMany(targetEntity="AbstractMediaDeclinationAttachment", mappedBy="mediaDeclination")
     */
    protected $mediaDeclinationAttachments;


    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->mediaDeclinationAttachments = new ArrayCollection();
    }

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

        return $this;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia(Media $media = null)
    {
        if ($this->media != $media) {
            if (null != $this->media) {
                $this->media->removeMediaDeclination($this);
                $this->media->addSize(-$this->size);
            }

            $this->media = $media;

            if (null != $media) {
                $media->addMediaDeclination($this);
                $media->addSize($this->size);
            }
        }

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        $this->setSize(filesize($this->file));

        return $this;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = strtoupper($fileExtension);

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $deltaSize = $size - $this->size;

        if (null != $this->media) {
            $this->media->addSize($deltaSize);
        }

        $this->size = $size;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getThumb()
    {
        return $this->thumb;
    }

    public function setThumb($thumb)
    {
        $this->thumb = $thumb;

        return $this;
    }

    public function generateThumb()
    {
    }

    public function getMediaId()
    {
        if (null === $this->getMedia()) {
            return null;
        }

        return $this->getMedia()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_media_declinations","detail_media_declination","detail_media"})
     */
    public function isMainDeclination()
    {
        return $this === $this->getMedia()->getMainDeclination();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_media_declinations","detail_media_declination","detail_media"})
     */
    //TODO : is there a way to automate this by returning the discrimator column "type" ?
    public static function getMediaDeclinationType()
    {
        return '';
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : 'mediacenter/uploads/'.$this->path;
    }

    /*public function getCmsFilesAttached()
    {
        return $this->cmsFilesAttached;
    }*/

    //TODO : public function addCmsFileAttached()
    //TODO : public function removeCmsFileAttached()

    public function isFileRequired()
    {
        return true;
    }

    /**
     * Get mediaDeclinationAttachments
     *
     * @return ArrayCollection|AbstractMediaDeclinationAttachment[]
     */
    public function getMediaDeclinationAttachments()
    {
        return $this->mediaDeclinationAttachments;
    }

    /**
     * Add mediaDeclinationAttachment
     *
     * @param AbstractMediaDeclinationAttachment $mediaDeclinationAttachment
     *
     * @return self
     */
    public function addMediaDeclinationAttachment(AbstractMediaDeclinationAttachment $mediaDeclinationAttachment)
    {
        if (!$this->mediaDeclinationAttachments->contains($mediaDeclinationAttachment)) {
            $this->mediaDeclinationAttachments->add($mediaDeclinationAttachment);
        }
        return $this;
    }

    /**
     * Remove mediaDeclinationAttachment
     *
     * @param AbstractMediaDeclinationAttachment $mediaDeclinationAttachment
     *
     * @return self
     */
    public function removeMediaDeclinationAttachment($mediaDeclinationAttachment)
    {
        if ($this->mediaDeclinationAttachments->contains($mediaDeclinationAttachment)) {
            $this->mediaDeclinationAttachments->removeElement($mediaDeclinationAttachment);
        }
        return $this;
    }
}
