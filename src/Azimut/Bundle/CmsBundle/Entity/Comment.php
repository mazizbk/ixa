<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 09:18:47
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_comment")
 */
class Comment
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var CmsFile
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFile")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $cmsFile;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $userName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $userEmail;

    /**
     * @var FrontofficeUser
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\FrontofficeUser")
     * @ORM\Column(nullable=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $frontofficeUser;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $rating;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isVisible = false;

    /**
     * @var AccessRightComment[]|ArrayCollection<AccessRightComment>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\AccessRightComment", mappedBy="comment")
     */
    protected $accessRights;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get cmsFile
     *
     * @return CmsFile
     */
    public function getCmsFile()
    {
        return $this->cmsFile;
    }

    /**
     * Set cmsFile
     *
     * @param CmsFile $cmsFile
     *
     * @return self
     */
    public function setCmsFile($cmsFile)
    {
        $this->cmsFile = $cmsFile;
        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return self
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * Get userEmail
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set userEmail
     *
     * @param string $userEmail
     *
     * @return self
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * Get frontofficeUser
     *
     * @return FrontofficeUser
     */
    public function getFrontofficeUser()
    {
        return $this->frontofficeUser;
    }

    /**
     * Set frontofficeUser
     *
     * @param FrontofficeUser $frontofficeUser
     *
     * @return self
     */
    public function setFrontofficeUser($frontofficeUser)
    {
        $this->frontofficeUser = $frontofficeUser;
        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get rating
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rating
     *
     * @param int $rating
     *
     * @return self
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * Get or set visible flag
     *
     * @param bool|null $isVisible
     *
     * @return bool|self
     */
    public function isVisible($isVisible = null)
    {
        if (null !== $isVisible) {
            $this->isVisible = $isVisible;
            return $this;
        }

        return $this->isVisible;
    }

    /**
     * {@inheritdoc}
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return 'Comment #'.$this->getId().' by '.$this->getUserName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessRightClassName()
    {
        return AccessRightComment::class;
    }

    /**
     * {@inheritdoc}
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public static function getParentsClassesSecurityContextObject()
    {
        return CmsFile::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getChildrenClassesSecurityContextObject()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParentsSecurityContextObject()
    {
        return [$this->cmsFile];
    }

    /**
     * {@inheritdoc}
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        return [];
    }
}
