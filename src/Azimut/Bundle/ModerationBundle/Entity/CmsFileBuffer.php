<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:34:34
 */

namespace Azimut\Bundle\ModerationBundle\Entity;

use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Gedmo\Timestampable\Traits\TimestampableEntity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\ModerationBundle\Entity\Repository\CmsFileBufferRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class CmsFileBuffer
{
    use TimestampableEntity;

    const TARGET_CMSFILE_CLASS = null;

    /**
     * @var integer
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $userEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=46)
     * @Groups({"detail_cms_file_buffer"})
     */
    protected $userIp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $domainName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $userLocale;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="5M")
     */
    protected $file;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     */
    protected $filePath;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $locale;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\Zone")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $targetZone;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $isArchived = false;

    /**
     * @var FrontofficeUser
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser")
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    protected $user;

    public static function getTargetCmsFileClass()
    {
        return static::TARGET_CMSFILE_CLASS;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserEmail()
    {
        return $this->userEmail;
    }

    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    public function getUserIp()
    {
        return $this->userIp;
    }

    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
        return $this;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        return $this;
    }

    public function getUserLocale()
    {
        return $this->userLocale;
    }

    public function setUserLocale($userLocale)
    {
        $this->userLocale = $userLocale;
        return $this;
    }

    //commun functions to display generic info about the file
    //overwrite this into extended classes
    /**
     * @VirtualProperty
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    public function getName($locale = null)
    {
        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    public static function getCmsFileBufferType()
    {
        return '';
    }

    public static function hasFile()
    {
        return false;
    }

    public function getFormType()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\', '\\Form\\Type\\', $type);
        $type.= 'Type';

        return $type;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTargetZone()
    {
        return $this->targetZone;
    }

    public function setTargetZone($targetZone)
    {
        $this->targetZone = $targetZone;
        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    public function getTargetPagePath()
    {
        if (null == $this->targetZone) {
            return null;
        }
        return '/'.$this->targetZone->getPage()->getFullSlug();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    public function getTargetZoneName()
    {
        if (null == $this->targetZone) {
            return null;
        }
        return $this->targetZone->getName();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_cms_files_buffer", "detail_cms_file_buffer"})
     */
    public function getTargetZoneId()
    {
        if (null == $this->targetZone) {
            return null;
        }
        return $this->targetZone->getId();
    }

    public function isArchived($isArchived = null)
    {
        if (null !== $isArchived) {
            $this->isArchived = $isArchived;
            return $this;
        }

        return $this->isArchived;
    }

    /**
     * Get user
     *
     * @return FrontofficeUser|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param FrontofficeUser|null $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
