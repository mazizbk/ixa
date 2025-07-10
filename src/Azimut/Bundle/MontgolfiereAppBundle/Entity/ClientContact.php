<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClientContact
 *
 * @ORM\Table(name="montgolfiere_client_contact")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\ClientContactRepository")
 * @UniqueEntity("emailAddress", message="Cette adresse est déjà utilisée par un autre contact")
 */
class ClientContact
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="emailAddress", type="string", length=255, nullable=true, unique=true)
     * @Assert\Email()
     */
    private $emailAddress;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(name="phoneNumber", type="phone_number", length=255, nullable=true)
     * @\Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber()
     */
    private $phoneNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="isHeadOfHumanResources", type="boolean")
     */
    private $isHeadOfHumanResources;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Client", inversedBy="contacts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $client;

    /**
     * @var FrontofficeUser
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser", inversedBy="clientContact", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true, unique=true)
     */
    private $frontUser;


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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return ClientContact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return ClientContact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return ClientContact
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set emailAddress
     *
     * @param string $emailAddress
     *
     * @return ClientContact
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get emailAddress
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return ClientContact
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set isHeadOfHumanResources
     *
     * @param boolean $isHeadOfHumanResources
     *
     * @return ClientContact
     */
    public function setIsHeadOfHumanResources($isHeadOfHumanResources)
    {
        $this->isHeadOfHumanResources = $isHeadOfHumanResources;

        return $this;
    }

    /**
     * Get isHeadOfHumanResources
     *
     * @return bool
     */
    public function getIsHeadOfHumanResources()
    {
        return $this->isHeadOfHumanResources;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return FrontofficeUser
     */
    public function getFrontUser()
    {
        return $this->frontUser;
    }

    /**
     * @param FrontofficeUser $frontUser
     * @return $this
     */
    public function setFrontUser($frontUser)
    {
        $this->frontUser = $frontUser;

        return $this;
    }

}
