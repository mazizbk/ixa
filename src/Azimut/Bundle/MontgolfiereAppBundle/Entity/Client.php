<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Traits\UploadableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Client
 *
 * @ORM\Table(name="montgolfiere_client")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\ClientRepository")
 */
class Client
{
    use TimestampableEntity;
    use UploadableEntity;

    const STATUS_CLIENT = 1,
        STATUS_PROSPECT = 2,
        STATUS_FORMER_CLIENT = 3
    ;

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
     * @ORM\Column(name="corporateName", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\Length(max="255")
     */
    private $corporateName;

    /**
     * @var string
     *
     * @ORM\Column(name="tradingName", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $tradingName;

    /**
     * Name that can be used inside of a question
     * @var string|null
     *
     * @ORM\Column(name="questionName", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $questionName;

    /**
     * @var int
     *
     * @ORM\Column(name="clientStatus", type="integer")
     * @Assert\NotNull()
     * @Assert\Choice(multiple=false, choices={
     *     Client::STATUS_CLIENT,
     *     Client::STATUS_PROSPECT,
     *     Client::STATUS_FORMER_CLIENT
     * })
     */
    private $clientStatus;

    /**
     * @var PostalAddress
     *
     * @ORM\Embedded(class="Azimut\Bundle\MontgolfiereAppBundle\Entity\PostalAddress", columnPrefix="postal_address_")
     */
    private $postalAddress;

    /**
     * @var int
     *
     * @ORM\Column(name="workforce", type="integer", nullable=true)
     * @Assert\Range(min="1")
     */
    private $workforce;

    /**
     * @var string
     *
     * @ORM\Column(name="legalStatus", type="string", length=255, nullable=true)
     */
    private $legalStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="activity", type="string", length=255, nullable=true)
     */
    private $activity;

    /**
     * @var string
     *
     * @ORM\Column(name="NAFCode", type="string", length=20, nullable=true)
     */
    private $NAFCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="turnover", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $turnover;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Gedmo\Slug(fields={"corporateName"})
     */
    private $slug;

    /**
     * @var ClientContact[]|ArrayCollection<ClientContact>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact", mappedBy="client")
     */
    private $contacts;

    /**
     * @var Campaign[]|ArrayCollection<Campaign>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", mappedBy="client")
     */
    private $campaigns;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }


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
     * Set corporateName
     *
     * @param string $corporateName
     *
     * @return Client
     */
    public function setCorporateName($corporateName)
    {
        $this->corporateName = $corporateName;

        return $this;
    }

    /**
     * Get corporateName
     *
     * @return string
     */
    public function getCorporateName()
    {
        return $this->corporateName;
    }

    /**
     * Set tradingName
     *
     * @param string $tradingName
     *
     * @return Client
     */
    public function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    /**
     * Get tradingName
     *
     * @return string
     */
    public function getTradingName()
    {
        return $this->tradingName;
    }

    public function getQuestionName(): ?string
    {
        return $this->questionName;
    }

    public function setQuestionName(?string $questionName): self
    {
        $this->questionName = $questionName;

        return $this;
    }

    /**
     * Set clientStatus
     *
     * @param integer $clientStatus
     *
     * @return Client
     */
    public function setClientStatus($clientStatus)
    {
        $this->clientStatus = $clientStatus;

        return $this;
    }

    /**
     * Get clientStatus
     *
     * @return int
     */
    public function getClientStatus()
    {
        return $this->clientStatus;
    }

    /**
     * Set postalAddress
     *
     * @param PostalAddress $postalAddress
     *
     * @return Client
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Get postalAddress
     *
     * @return PostalAddress
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Set workforce
     *
     * @param integer $workforce
     *
     * @return Client
     */
    public function setWorkforce($workforce)
    {
        $this->workforce = $workforce;

        return $this;
    }

    /**
     * Get workforce
     *
     * @return int
     */
    public function getWorkforce()
    {
        return $this->workforce;
    }

    /**
     * Set legalStatus
     *
     * @param string $legalStatus
     *
     * @return Client
     */
    public function setLegalStatus($legalStatus)
    {
        $this->legalStatus = $legalStatus;

        return $this;
    }

    /**
     * Get legalStatus
     *
     * @return string
     */
    public function getLegalStatus()
    {
        return $this->legalStatus;
    }

    /**
     * Set activity
     *
     * @param string $activity
     *
     * @return Client
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set NAFCode
     *
     * @param string $NAFCode
     *
     * @return Client
     */
    public function setNAFCode($NAFCode)
    {
        $this->NAFCode = $NAFCode;

        return $this;
    }

    /**
     * Get nAFCode
     *
     * @return string
     */
    public function getNAFCode()
    {
        return $this->NAFCode;
    }

    /**
     * Set turnover
     *
     * @param integer $turnover
     *
     * @return Client
     */
    public function setTurnover($turnover)
    {
        $this->turnover = $turnover;

        return $this;
    }

    /**
     * Get turnover
     *
     * @return integer
     */
    public function getTurnover()
    {
        return $this->turnover;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Client
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Client
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return ClientContact[]|ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @return Campaign[]|ArrayCollection<Campaign>
     */
    public function getCampaigns()
    {
        if($this instanceof Proxy && !$this->__isInitialized()) {
            $this->__load();
        }

        return new ArrayCollection($this->campaigns->toArray());
    }

}

