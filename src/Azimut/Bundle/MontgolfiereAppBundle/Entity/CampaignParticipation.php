<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Azimut\Bundle\MontgolfiereAppBundle\Validator\Constraint\UniqueEmailParticipation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CampaignParticipation
 *
 * @ORM\Table(name="montgolfiere_campaign_participation")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignParticipationRepository")
 * @UniqueEmailParticipation()
 * @Gedmo\SoftDeleteable(fieldName="archivedAt")
 */
class CampaignParticipation
{

    const GENDER_MAN = 1,
        GENDER_WOMAN = 2,
        GENDER_OTHER = 3,
        GENDER_DO_NOT_ANSWER = 4
    ;

    const SENIORITY_LESS_THAN_2 = 1,
        SENIORITY_LESS_THAN_5 = 5,
        SENIORITY_LESS_THAN_10 = 10,
        SENIORITY_LESS_THAN_20 = 20,
        SENIORITY_MORE_THAN_20 = 21
    ;

    const EDUCATION_LEVEL_CAP_BEP = 1,
        EDUCATION_BAC = 2,
        EDUCATION_BAC2 = 3,
        EDUCATION_BAC3 = 4,
        EDUCATION_BAC4 = 5,
        EDUCATION_BAC5 = 6,
        EDUCATION_BAC8 = 7,
        EDUCATION_OTHER = 8
    ;

    const CSP_OPERATING_FARMER = 1,
        CSP_ARTISAN_MERCHANT_COMPANY_DIRECTOR = 2,
        CSP_EXECUTIVE_INTELLECTUAL_PROFESSION = 3,
        CSP_INTERMEDIATE_PROFESSION = 4,
        CSP_QUALIFIED_EMPLOYEE = 5,
        CSP_UNQUALIFIED_EMPLOYEE = 6,
        CSP_SKILLED_WORKER = 7,
        CSP_UNSKILLED_WORKER = 8
    ;

    const AGE_15_17 = 1,
        AGE_18_24 = 2,
        AGE_25_34 = 3,
        AGE_35_49 = 4,
        AGE_50_64 = 5,
        AGE_65_PLUS = 6
    ;

    const MARITAL_STATUS_SINGLE = 1,
        MARITAL_STATUS_COHABITATION = 2,
        MARITAL_STATUS_MARRIED = 3,
        MARITAL_STATUS_DIVORCED = 4,
        MARITAL_STATUS_WIDOWER = 5
    ;

    const MANAGEMENT_RESPONSIBILITIES_NONE = 1,
        MANAGEMENT_RESPONSIBILITIES_MANAGER = 2,
        MANAGEMENT_RESPONSIBILITIES_MANAGER_OF_MANAGERS = 3
    ;

    const AVERAGE_MODE_0_10 = 1,
        AVERAGE_MODE_m10_10 = 2
    ;

    use TimestampableEntity;

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
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $lastName;

    /**
     * @var int
     *
     * @ORM\Column(name="seniority", type="smallint", nullable=true)
     * @Assert\Choice(choices={
     *  CampaignParticipation::SENIORITY_LESS_THAN_2,
     *  CampaignParticipation::SENIORITY_LESS_THAN_5,
     *  CampaignParticipation::SENIORITY_LESS_THAN_10,
     *  CampaignParticipation::SENIORITY_LESS_THAN_20,
     *  CampaignParticipation::SENIORITY_MORE_THAN_20
     * })
     */
    private $seniority;

    /**
     * @var CampaignSegment
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment", inversedBy="participations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $segment;

    /**
     * @var string
     *
     * @ORM\Column(name="managerName", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $managerName;

    /**
     * @var string
     *
     * @ORM\Column(name="emailAddress", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Assert\Email()
     */
    private $emailAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=30, nullable=true)
     * @Assert\Length(max="30")
     */
    private $phoneNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="gender", type="smallint", nullable=true)
     * @Assert\Choice(choices={
     *  CampaignParticipation::GENDER_MAN,
     *  CampaignParticipation::GENDER_WOMAN,
     *  CampaignParticipation::GENDER_OTHER,
     *  CampaignParticipation::GENDER_DO_NOT_ANSWER
     * })
     */
    private $gender;

    /**
     * @var CampaignParticipationAnswer[]|ArrayCollection<CampaignParticipationAnswer>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationAnswer", mappedBy="participation", cascade={"persist"})
     */
    private $answers;

    /**
     * @var CampaignParticipationOpinion
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationOpinion", mappedBy="participation")
     */
    private $opinion;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $finished = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $education;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $csp;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $age;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maritalStatus;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $managementResponsibilities;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=5)
     */
    private $residenceState;

    /**
     * Stores the campaign until the segment is set
     * @var Campaign
     */
    private $campaign;

    /**
     * @var CampaignSortingFactorValue[]|ArrayCollection<CampaignSortingFactorValue>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue")
     * @ORM\JoinTable(name="montgolfiere_campaign_participation_sorting_factor_value")
     */
    private $sortingFactorsValues;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $WBEAlertSent = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $contactRequested = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $contactRefused = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $IPAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $token;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $archivedAt;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->sortingFactorsValues = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return $this
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
     * @return $this
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
     * Set seniority
     *
     * @param integer $seniority
     *
     * @return $this
     */
    public function setSeniority($seniority)
    {
        $this->seniority = $seniority;

        return $this;
    }

    /**
     * Get seniority
     *
     * @return int
     */
    public function getSeniority()
    {
        return $this->seniority;
    }

    /**
     * Set segment
     *
     * @param CampaignSegment $segment
     *
     * @return $this
     */
    public function setSegment(CampaignSegment $segment)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get segment
     *
     * @return CampaignSegment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Set managerName
     *
     * @param string $managerName
     *
     * @return $this
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;

        return $this;
    }

    /**
     * Get managerName
     *
     * @return string
     */
    public function getManagerName()
    {
        return $this->managerName;
    }

    /**
     * Set emailAddress
     *
     * @param string $emailAddress
     *
     * @return $this
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
     * @return $this
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
     * Set gender
     *
     * @param integer $gender
     *
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->segment?$this->segment->getCampaign():$this->campaign;
    }

    /**
     * @param Campaign $campaign
     * @return $this
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return CampaignParticipationAnswer[]|ArrayCollection<CampaignParticipationAnswer>
     */
    public function getAnswers()
    {
        return new ArrayCollection($this->answers->toArray()); // Prevent collection from being updated from the outside
    }

    public function addAnswer(CampaignParticipationAnswer $answer)
    {
        $this->answers->add($answer);

        return $this;
    }

    public function getAnswer(CampaignSegmentStep $step): ?CampaignParticipationAnswer
    {
        foreach ($this->answers as $answer) {
            if ($answer->getStep() === $step) {
                return $answer;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * @return int
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * @param int $education
     * @return $this
     */
    public function setEducation($education)
    {
        $this->education = $education;

        return $this;
    }

    /**
     * @return int
     */
    public function getCsp()
    {
        return $this->csp;
    }

    /**
     * @param int $csp
     * @return $this
     */
    public function setCsp($csp)
    {
        $this->csp = $csp;

        return $this;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param int $age
     * @return $this
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * @param int $maritalStatus
     * @return $this
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getManagementResponsibilities()
    {
        return $this->managementResponsibilities;
    }

    /**
     * @param int $managementResponsibilities
     * @return $this
     */
    public function setManagementResponsibilities($managementResponsibilities)
    {
        $this->managementResponsibilities = $managementResponsibilities;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getResidenceState()
    {
        return $this->residenceState;
    }

    /**
     * @param string $residenceState
     * @return $this
     */
    public function setResidenceState($residenceState)
    {
        $this->residenceState = $residenceState;

        return $this;
    }

    /**
     * @param bool $finished
     * @return $this
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    public function getWellBeingScore()
    {
        return WBEManager::getWeightedScore($this, WBEManager::WEIGHTED_SCORE_WB);
    }

    public function getEngagementScore()
    {
        return WBEManager::getWeightedScore($this, WBEManager::WEIGHTED_SCORE_E);
    }

    public function getSortingFactorValue(CampaignSortingFactor $sortingFactor): ?CampaignSortingFactorValue
    {
        foreach ($this->sortingFactorsValues as $value) {
            if($value->getSortingFactor() !== $sortingFactor) {
                continue;
            }

            return $value;
        }

        return null;
    }

    public function setSortingFactorValue(CampaignSortingFactor $sortingFactor, ?CampaignSortingFactorValue $value): self
    {
        foreach ($this->sortingFactorsValues as $sortingFactorsValue) {
            if($sortingFactorsValue->getSortingFactor() !== $sortingFactor) {
                continue;
            }
            $this->sortingFactorsValues->removeElement($sortingFactorsValue);
        }
        if($value) {
            $this->sortingFactorsValues->add($value);
        }

        return $this;
    }

    /**
     * @return CampaignSortingFactorValue[]|ArrayCollection
     */
    public function getSortingFactorsValues()
    {
        return $this->sortingFactorsValues;
    }

    public function isWBEAlertSent(): bool
    {
        return $this->WBEAlertSent;
    }

    public function setWBEAlertSent(bool $WBEAlertSent): self
    {
        $this->WBEAlertSent = $WBEAlertSent;

        return $this;
    }

    public function isContactRequested(): bool
    {
        return $this->contactRequested;
    }

    public function setContactRequested(bool $contactRequested): self
    {
        $this->contactRequested = $contactRequested;

        return $this;
    }

    public function isContactRefused(): bool
    {
        return $this->contactRefused;
    }

    public function setContactRefused(bool $contactRefused): self
    {
        $this->contactRefused = $contactRefused;

        return $this;
    }

    public function getIPAddress(): ?string
    {
        return $this->IPAddress;
    }

    public function setIPAddress(?string $IPAddress): CampaignParticipation
    {
        $this->IPAddress = $IPAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return CampaignParticipation
     */
    public function setToken(?string $token): CampaignParticipation
    {
        $this->token = $token;

        return $this;
    }

    public function getArchivedAt(): ?\DateTime
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTime $archivedAt): self
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }


}
