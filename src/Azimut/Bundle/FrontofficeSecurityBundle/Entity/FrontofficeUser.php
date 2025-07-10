<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 10:31:10
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Serializable;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeSecurityBundle\Entity\Repository\FrontofficeUserRepository")
 * @ORM\Table(name="frontoffice_security_user")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="this.user.already.exists"
 * )
 * @ORM\InheritanceType("JOINED")
 * @DynamicInheritanceMap
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceSubClass(discriminatorValue="base")
 */
class FrontofficeUser implements UserInterface, Serializable
{
    use TimestampableEntity, BlameableEntity;

    const ROLE_DEFAULT = 'ROLE_FRONT_USER';
    const RESET_TOKEN_LIFETIME = 120; // lifetime in minutes

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user", "always"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=190)
     * @Assert\Email()
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user"})
     */
    protected $email;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $resetToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetTokenDateTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user"})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user"})
     */
    protected $lastName;

    /**
     * @var string[]
     *
     * @ORM\Column(name="roles", type="array")
     * @Groups({"detail_frontoffice_user"})
     */
    protected $roles = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user"})
     */
    protected $isActive = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_frontoffice_users", "detail_frontoffice_user"})
     */
    protected $isEmailConfirmed = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $confirmEmailToken;

    /**
     * @var FrontofficeUserAddress
     *
     * @ORM\OneToOne(targetEntity="FrontofficeUserAddress", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"detail_frontoffice_user"})
     */
    protected $address;

    /**
     * @var FrontofficeUserAddress
     *
     * @ORM\OneToOne(targetEntity="FrontofficeUserAddress", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"detail_frontoffice_user"})
     */
    protected $deliveryAddress;

    /**
     * @var ClientContact
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact", mappedBy="frontUser")
     */
    private $clientContact;

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
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->email = $username;
        return $this;
    }

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set plain password
     *
     * @param string $plainPassword
     *
     * @return self
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set first name
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = ucfirst($firstName);
        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set last name
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = ucfirst($lastName);
        return $this;
    }

    public function getFullname()
    {
        return implode(' ', [$this->getFirstName(), $this->getLastName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_DEFAULT;
        return array_unique($roles);
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Get reset token
     *
     * @return string
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * Set reset token
     *
     * @param string $resetToken
     *
     * @return self
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;
        $this->setResetTokenDateTime(null==$resetToken? null:new \DateTime());
        return $this;
    }

    /**
     * Get reset token date time
     *
     * @return \DateTime
     */
    public function getResetTokenDateTime()
    {
        return $this->resetTokenDateTime;
    }

    /**
     * Set reset token date time
     *
     * @param \DateTime|null $resetTokenDateTime
     *
     * @return self
     */
    public function setResetTokenDateTime($resetTokenDateTime)
    {
        $this->resetTokenDateTime = $resetTokenDateTime;
        return $this;
    }

    /**
     * Get or set user active status
     *
     * @param boolean|null
     *
     * @return boolean|$this
     */
    public function isActive($isActive = null)
    {
        if (null !== $isActive) {
            $this->isActive = $isActive;
            return $this;
        }

        return $this->isActive;
    }

    /**
     * Get or set email validation status
     *
     * @param boolean|null
     *
     * @return boolean|$this
     */
    public function isEmailConfirmed($isEmailConfirmed = null)
    {
        if (null !== $isEmailConfirmed) {
            $this->isEmailConfirmed = $isEmailConfirmed;
            if (true === $isEmailConfirmed) {
                $this->confirmEmailToken = null;
            }
            return $this;
        }

        return $this->isEmailConfirmed;
    }

    /**
    * Get confirmEmailToken
    *
    * @return string
    */
    public function getConfirmEmailToken()
    {
        return $this->confirmEmailToken;
    }

    /**
    * Set confirmEmailToken
    *
    * @param string $confirmEmailToken
    *
    * @return self
    */
    public function setConfirmEmailToken($confirmEmailToken)
    {
        $this->confirmEmailToken = $confirmEmailToken;
        return $this;
    }

    /**
     * @return ClientContact
     */
    public function getClientContact()
    {
        return $this->clientContact;
    }

    /**
     * Get Address
     *
     * @return FrontofficeUserAddress|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set Address
     *
     * @param FrontofficeUserAddress|null $address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return FrontofficeUserAddress|null
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set deliveryAddress
     *
     * @param FrontofficeUserAddress|null $deliveryAddress
     *
     * @return self
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
        ) = unserialize($serialized);
    }
}
