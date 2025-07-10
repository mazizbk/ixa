<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_folder")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MediacenterBundle\Entity\Repository\FolderRepository")
 */
class Folder
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list_folders","detail_folder","list_medias","detail_media","list_trash_bin","security_access_right_obj"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ\._\s\-'’()]+$/i",
     *     message="this.value.must.not.contain.special.characters"
     * )
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "folder.name.cannot.be.longer.than._limit_.characters"
     * )
     *
     * @Groups({"list_folders","detail_folder","list_medias","detail_media","list_trash_bin"})
     */
    protected $name;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="subfolders")
     * @ORM\JoinColumn(name="parent_folder_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    protected $parentFolder;

    /**
     * @var Folder|null
     *
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="trashed_parent_folder_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    protected $trashedParentFolder;

    /**
     * @var Folder[]|ArrayCollection<Folder>
     *
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parentFolder", cascade={"remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     *
     * @Groups({"list_folders"})
     */
    protected $subfolders;

    /**
     * @var Media[]|ArrayCollection<Media>
     * @ORM\OneToMany(targetEntity="Media", mappedBy="folder", cascade={"remove"})
     *
     * @Groups({"detail_folder"})
     */
    protected $medias;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
     */
    protected $quota;

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
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
     * @Groups({"list_folders","detail_folder"})
     */
    protected $size = 0;

    /**
     * @var AccessRightFolder[]|ArrayCollection<AccessRightFolder>
     *
     * @ORM\OneToMany(targetEntity="AccessRightFolder", mappedBy="folder")
     */
    protected $accessRights;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->subfolders = new ArrayCollection();
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = ucfirst($name);

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setParentFolder(Folder $folder = null)
    {
        if (null != $this->parentFolder) {
            $this->parentFolder->addSize(-$this->size);
        }
        $this->parentFolder = $folder;
        if (null !== $folder && $this->parentFolder !== $folder) {
            $folder->addSubfolder($this);
        }
        if (null != $folder) {
            $folder->addSize($this->size);
        }

        return $this;
    }

    /**
     * @return Folder|null
     */
    public function getParentFolder()
    {
        return $this->parentFolder;
    }

    public function addSubFolder(Folder $folder)
    {
        if (!$this->subfolders->contains($folder)) {
            $this->subfolders->add($folder);

            $folder->setParentFolder($this);
        }

        return $this;
    }

    public function removeSubFolder(Folder $folder)
    {
        if ($this->subfolders->contains($folder)) {
            $this->subfolders->removeElement($folder);
        }

        return $this;
    }

    public function getSubfolders()
    {
        return $this->subfolders;
    }

    public function hasSubfolders()
    {
        return count($this->subfolders) > 0;
    }

    public function isRootFolder()
    {
        return $this->parentFolder ? false : true;
    }

    public function getMedias()
    {
        return $this->medias;
    }

    public function hasMedias()
    {
        return count($this->medias) > 0;
    }

    public function addMedia(Media $media)
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);

            $media->setFolder($this);
        }

        return $this;
    }

    public function removeMedia(Media $media)
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
        }

        return $this;
    }

    /**
     *
     * @VirtualProperty
     * @Groups({"detail_folder"})
     */
    public function getParentFolderId()
    {
        if (null === $this->getParentFolder()) {
            return null;
        }

        return $this->getParentFolder()->getId();
    }

    public function getQuota()
    {
        $quota = $this->quota;
        if (null === $quota && null != $parentFolder = $this->getParentFolder()) {
            $quota = $parentFolder->getQuota();
        }

        return $quota;
    }

    public function setQuota($quota)
    {
        //TODO: check that the sum of all root folders quota are not overflowing the global quota

        $this->quota = $quota;

        return $this;
    }

    public function getFullName()
    {
        $parentFolder = $this->getParentFolder();
        if (!$parentFolder) {
            return '/'.$this->getName();
        } else {
            return $parentFolder->getFullName().'/'.$this->getName();
        }
    }

    /**
     * Set trashed
     *
     * @param  boolean $trashed
     * @return Folder
     */
    public function setTrashed($trashed, $unlinkFromParent = true)
    {
        if ($trashed == $this->trashed) {
            return $this;
        }

        if ($trashed && null === $folder = $this->getParentFolder()) {
            throw new \InvalidArgumentException("Root folders can not be trashed");
        }

        if (null === $trashed) {
            $trashed = false;
        }
        $this->trashed = $trashed;

        if ($trashed) {
            if (true === $unlinkFromParent) {
                $folder->removeSubfolder($this);
                $this->setTrashedParentFolder($this->getParentFolder());
                $this->setParentFolder(null);
                $this->setTrashedFolderPath($folder->getFullName());
            }

            $this->setTrashedDate(new \DateTime());
        } else {
            $this->setTrashedDate(null);
        }

        // Set trashed flag in all children
        foreach ($this->subfolders as $subfolder) {
            $subfolder->setTrashed($trashed, false);
        }
        foreach ($this->medias as $media) {
            $media->setTrashed($trashed, false);
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

    public function getTrashedParentFolder()
    {
        return $this->trashedParentFolder;
    }

    public function setTrashedParentFolder($trashedParentFolder)
    {
        $this->trashedParentFolder = $trashedParentFolder;
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

    /**
     * Get size
     *
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set size
     *
     * @param int|null $size
     *
     * @return self
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function addSize($delta)
    {
        $this->size += $delta;
        if (null != $this->parentFolder) {
            $this->parentFolder->addSize($delta);
        }
    }

    public static function getAccessRightClassName()
    {
        return AccessRightFolder::class;
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'folder';
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Folder::class;
    }

    public function getParentsSecurityContextObject()
    {
        if ($this->getParentFolder()) {
            return [$this->getParentFolder()];
        }
        return [];
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     * @fixme Add Files to children
     */
    public function getChildrenSecurityContextObject()
    {
        return array_merge($this->getSubfolders()->toArray(), $this->getMedias()->toArray());
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [self::class, Media::class];
    }
}
