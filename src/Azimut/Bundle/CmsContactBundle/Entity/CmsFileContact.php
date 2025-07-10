<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 10:57:46
 */

namespace Azimut\Bundle\CmsContactBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_contact")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="contact")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 */
class CmsFileContact extends CmsFile
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file"})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file"})
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $country;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file"})
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file"})
     */
    protected $email;

    /**
     * @var AccessRightCmsFileContact
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileContact", mappedBy="cmsfilecontact")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public static function getCmsFileType()
    {
        return 'contact';
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAbstract($locale = null)
    {
        $abstract = $this->getCity();

        if ('' != $abstract) {
            $abstract .= ' - ';
        }

        $abstract .= $this->getCountry();

        return $abstract;
    }

    public function getAccessRightClassName()
    {
        return AccessRightCmsFileContact::class;
    }
}
