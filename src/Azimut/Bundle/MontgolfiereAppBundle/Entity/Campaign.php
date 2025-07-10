<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Campaign
 *
 * @ORM\Table(name="montgolfiere_campaign")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignRepository")
 */
class Campaign
{
    const FIELD_STATUS_OPTIONAL = 1;
    const FIELD_STATUS_REQUIRED = 2;
    const FIELD_STATUS_DISABLED = 3;

    static $defaultFieldsStatus = [
        'seniority' => self::FIELD_STATUS_REQUIRED,
        'gender' => self::FIELD_STATUS_OPTIONAL,
        'firstName' => self::FIELD_STATUS_OPTIONAL,
        'lastName' => self::FIELD_STATUS_OPTIONAL,
        'emailAddress' => self::FIELD_STATUS_REQUIRED,
        'phoneNumber' => self::FIELD_STATUS_OPTIONAL,
        'managerName' => self::FIELD_STATUS_OPTIONAL,
        'education' => self::FIELD_STATUS_DISABLED,
        'csp' => self::FIELD_STATUS_DISABLED,
        'age' => self::FIELD_STATUS_DISABLED,
        'maritalStatus' => self::FIELD_STATUS_DISABLED,
        'managementResponsibilities' => self::FIELD_STATUS_DISABLED,
        'position' => self::FIELD_STATUS_DISABLED,
        'residenceState' => self::FIELD_STATUS_DISABLED,
    ];
    static $configurableFields = [
        'seniority', 'gender', 'firstName', 'lastName', 'emailAddress', 'phoneNumber', 'managerName',
        'education', 'csp', 'age', 'maritalStatus', 'managementResponsibilities', 'position', 'residenceState',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $name;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Client", inversedBy="campaigns")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $client;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date", nullable=true)
     * @Assert\Date()
     */
    private $endDate;

    /**
     * @var string[]
     *
     * @ORM\Column(name="introduction", type="json_array", nullable=true)
     */
    private $introduction = [];

    /**
     * @var int
     *
     * @ORM\Column(name="expectedAnswers", type="integer", nullable=true)
     * @Assert\Range(min="1")
     */
    private $expectedAnswers;

    /**
     * @var CampaignSegment[]|ArrayCollection<CampaignSegment>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment", mappedBy="campaign")
     */
    private $segments;

    /**
     * @var CampaignAnalysisGroup[]|ArrayCollection<CampaignAnalysisGroup>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAnalysisGroup", mappedBy="campaign")
     */
    private $analysisGroups;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $questionnaireToken;

    /**
     * @var array
     *
     * @ORM\Column(type="array", length=65535)
     */
    private $fieldsStatus = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=false)
     * @Assert\Choice(multiple=true, min=1, choices={"fr", "en"})
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $allowedLanguages = ['fr'];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $useNewGauge = true;

    /**
     * @var CampaignSortingFactor[]|ArrayCollection<CampaignSortingFactor>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor", mappedBy="campaign", cascade={"persist"})
     */
    private $sortingFactors;

    /**
     * @var CampaignAutomaticAffectation[]|ArrayCollection<CampaignAutomaticAffectation>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation", mappedBy="campaign")
     */
    private $automaticAffectations;

    /**
     * @var Consultant[]|ArrayCollection<Consultant>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant", inversedBy="campaigns")
     * @ORM\JoinTable(name="montgolfiere_campaign_consultant")
     */
    private $consultants;

    /**
     * @var string[]
     *
     * @ORM\Column(name="opening_message", type="json_array", nullable=true)
     */
    private $openingMessage = [];

    /**
     * @var CampaignParticipation[]
     */
    private $participations;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $additionalQuestionsAvailableForClient;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $additionalQuestionsAvailableForConsultant;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     * @Serializer\Groups({"backoffice_campaigns_read"})
     */
    private $questionsAvailableForConsultantVerbatimExport;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $clientAreaAllowHouseView = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $consultantAreaAllowCartographyView = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $consultantAreaAllowHouseView = false;

    /**
     * @var AnalysisVersion
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $analysisVersion;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $allowOtherGender = true;

    public function __construct()
    {
        $this->segments = new ArrayCollection();
        $this->fieldsStatus = self::$defaultFieldsStatus;
        foreach ($this->allowedLanguages as $allowedLanguage) {
            $this->introduction[$allowedLanguage] = '';
            $this->openingMessage[$allowedLanguage] = '';
        }
        $this->sortingFactors = new ArrayCollection();
        $this->consultants = new ArrayCollection();
        $this->additionalQuestionsAvailableForClient = [];
        $this->additionalQuestionsAvailableForConsultant = [];
        $this->questionsAvailableForConsultantVerbatimExport = [];
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
     * Set name
     *
     * @param string $name
     *
     * @return Campaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set client
     *
     * @param Client $client
     *
     * @return Campaign
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Campaign
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Campaign
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set introduction
     *
     * @param string[] $introduction
     *
     * @return Campaign
     */
    public function setIntroduction(array $introduction)
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function setIntroductionLocale($introduction, $locale)
    {
        $this->introduction[$locale] = $introduction;

        return $this;
    }

    /**
     * Get introduction
     *
     * @return string[]
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set expectedAnswers
     *
     * @param integer $expectedAnswers
     *
     * @return Campaign
     */
    public function setExpectedAnswers($expectedAnswers)
    {
        $this->expectedAnswers = $expectedAnswers;

        return $this;
    }

    /**
     * Get expectedAnswers
     *
     * @return int
     */
    public function getExpectedAnswers()
    {
        return $this->expectedAnswers;
    }

    /**
     * @return CampaignSegment[]|ArrayCollection<CampaignSegment>
     */
    public function getSegments()
    {
        return new ArrayCollection($this->segments->toArray()); // Prevent collection from being updated from the outside
    }

    /**
     * @return CampaignSegment[]|ArrayCollection<CampaignSegment>
     */
    public function getValidSegments()
    {
        return $this->getSegments()->filter(function(CampaignSegment $segment): bool {return $segment->isValid();});
    }

    public function addSegment(CampaignSegment $segment)
    {
        $segment->setCampaign($this);
        $this->segments->add($segment);

        return $this;
    }

    public function removeSegment(CampaignSegment $segment)
    {
        $this->segments->removeElement($segment);

        return $this;
    }

    public function getParticipationsCount()
    {
        return count($this->getParticipations());
    }

    /**
     * @return string
     */
    public function getQuestionnaireToken()
    {
        return $this->questionnaireToken;
    }

    /**
     * @param string $questionnaireToken
     * @return $this
     */
    public function setQuestionnaireToken($questionnaireToken)
    {
        $this->questionnaireToken = $questionnaireToken;

        return $this;
    }

    public function hasValidSegment()
    {
        foreach ($this->segments as $segment) {
            if($segment->isValid()) {
                return true;
            }
        }

        return false;
    }

    public function hasValidLocalizedSegment($locale)
    {
        foreach ($this->segments as $segment) {
            if($segment->getLocale() === $locale && $segment->isValid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return CampaignParticipation[]
     */
    public function getParticipations()
    {
        if($this->participations !== null){
            return $this->participations;
        }
        $this->participations = [];

        foreach ($this->segments as $segment) {
            foreach ($segment->getParticipations() as $participation) {
                if(!$participation->isFinished() || $participation->getArchivedAt() !== null) {
                    continue;
                }
                $this->participations[] = $participation;
            }
        }

        return $this->participations;
    }

    /**
     * @param CampaignParticipation[] $participations
     * @return $this
     */
    public function setParticipations($participations)
    {
        $this->participations = $participations;

        return $this;
    }

    public function getProgressColor()
    {
        $ratio = $this->expectedAnswers ? $this->getParticipationsCount() / $this->expectedAnswers : 0;
        return CampaignManager::getProgressColor($ratio);
    }

    /**
     * @return array
     */
    public function getFieldsStatus()
    {
        return $this->fieldsStatus;
    }

    /**
     * @param array $fieldsStatus
     * @return $this
     */
    public function setFieldsStatus($fieldsStatus)
    {
        $this->fieldsStatus = $fieldsStatus;

        return $this;
    }

    public function getFieldStatus($fieldName)
    {
        if(array_key_exists($fieldName, $this->fieldsStatus)) {
            return $this->fieldsStatus[$fieldName];
        }
        if(array_key_exists($fieldName, self::$defaultFieldsStatus)) {
            return self::$defaultFieldsStatus[$fieldName];
        }

        throw new \InvalidArgumentException('Unknown field '.$fieldName);
    }

    /**
     * @return array
     */
    public function getAllowedLanguages()
    {
        return $this->allowedLanguages;
    }

    /**
     * @param array $allowedLanguages
     * @return $this
     */
    public function setAllowedLanguages($allowedLanguages)
    {
        $this->allowedLanguages = $allowedLanguages;

        return $this;
    }

    public function isUseNewGauge(): bool
    {
        return $this->useNewGauge;
    }

    public function setUseNewGauge(bool $useNewGauge):self
    {
        $this->useNewGauge = $useNewGauge;

        return $this;
    }

    /**
     * @return CampaignSortingFactor[]|ArrayCollection
     */
    public function getSortingFactors()
    {
        return $this->sortingFactors;
    }

    public function addSortingFactor(CampaignSortingFactor $sortingFactor): self
    {
        if(!$this->sortingFactors->contains($sortingFactor)) {
            $this->sortingFactors->add($sortingFactor->setCampaign($this));
        }

        return $this;
    }

    public function sortingFactorValueParticipations(CampaignSortingFactorValue $value): array
    {
        $participations = new ArrayCollection($this->getParticipations());
        $participations = $participations->filter(function(CampaignParticipation $participation) use($value){
            return $participation->getSortingFactorsValues()->contains($value);
        });

        return $participations->toArray();
    }

    /**
     * @return CampaignAutomaticAffectation[]|ArrayCollection
     */
    public function getAutomaticAffectations()
    {
        return $this->automaticAffectations;
    }

    public function getWellBeingAverage(){
        if (count($this->getParticipations()) == 0){
            return 0;
        }
        $wellBeingAverage = 0;
        foreach ($this->getParticipations() as $participation) {
            $wellBeingAverage+= $participation->getWellBeingScore();
        }
        $wellBeingAverage/= count($this->getParticipations());

        return $wellBeingAverage;
    }

    public function getEngagementAverage(){
        if (count($this->getParticipations()) == 0){
            return 0;
        }
        $engagementAverage = 0;
        foreach ($this->getParticipations() as $participation) {
            $engagementAverage+= $participation->getEngagementScore();
        }
        $engagementAverage/= count($this->getParticipations());

        return $engagementAverage;
    }

    public function getAdditionalQuestions(array $types, $userType = null){
        $questions = [];
        //Prepare array with additional questions and list of values for each
        foreach($this->segments as $segment){
            foreach($segment->getSteps() as $step){
                if ($step->getType() != CampaignSegmentStep::TYPE_QUESTION){
                    continue;
                }
                if(!in_array($step->getQuestion()->getType(), $types)){
                    continue;
                }
                if($userType == 'client' && !in_array($step->getQuestion()->getId(), $this->additionalQuestionsAvailableForClient)){
                    continue;
                }
                if($userType == 'consultant' && !in_array($step->getQuestion()->getId(), $this->additionalQuestionsAvailableForConsultant)){
                    continue;
                }
                if(!isset($questions[$step->getQuestion()->getId()])) {
                    $questions[$step->getQuestion()->getId()] = ['question' => $step, 'segments' => [], 'participationCount'=> 0];
                    if (in_array($step->getQuestion()->getType(), [Question::TYPE_CHOICES_UNIQUE, Question::TYPE_CHOICES_MULTIPLES])) {
                        $possibleValues = array_map(function ($value) {
                            if (($separatorPos = strpos($value, '|')) !== false) {
                                return substr($value, 0, $separatorPos);
                            }

                            return str_replace("\r", "", $value);
                        }, explode("\n", $step->getQuestion()->getPossibleValues()));
                        $possibleValues = array_combine($possibleValues, array_fill(0, count($possibleValues), 0));
                        $questions[$step->getQuestion()->getId()]['answers'] = $possibleValues;
                    }elseif($step->getQuestion()->getType() == Question::TYPE_TRUE_FALSE){
                        $questions[$step->getQuestion()->getId()]['answers'] = ['true' => 0, 'false' => 0];
                    }elseif($step->getQuestion()->getType() == Question::TYPE_SATISFACTION_GAUGE){
                        $questions[$step->getQuestion()->getId()]['answers'] = array_combine(range(0, $step->getQuestion()->getGaugeMaxValue()), array_fill(0, $step->getQuestion()->getGaugeMaxValue()+1, 0));
                    }else{
                        $questions[$step->getQuestion()->getId()]['answers'] = 0;
                    }
                }
                $questions[$step->getQuestion()->getId()]['segments'][$step->getSegment()->getId()] = $step;
            }
        }

        //Count participations answers for each question
        foreach($this->participations as $participation){
            foreach($questions as $questionId => $question) {
                if(in_array($participation->getSegment()->getId(), array_keys($question['segments']))) {
                    $answer = $participation->getAnswer($question['segments'][$participation->getSegment()->getId()]);
                    if($answer && isset($question['participationCount'])){
                        $questions[$questionId]['participationCount'] ++;
                        switch ($questions[$questionId]['question']->getQuestion()->getType()){
                            case Question::TYPE_CHOICES_UNIQUE:
                            case Question::TYPE_SATISFACTION_GAUGE:
                                if (isset($questions[$questionId]['answers'][$answer->getOpenAnswer()])) {
                                    $questions[$questionId]['answers'][$answer->getOpenAnswer()]++;
                                }
                                break;
                            case Question::TYPE_CHOICES_MULTIPLES:
                                if (is_array($answer->getOpenAnswer())) {
                                    foreach($answer->getOpenAnswer() as $answer){
                                        if (isset($questions[$questionId]['answers'][$answer])) {
                                            $questions[$questionId]['answers'][$answer]++;
                                        }
                                    }
                                }
                                break;
                            case Question::TYPE_TRUE_FALSE:
                                if (in_array($answer->getOpenAnswer(), ['true', 'false'])) {
                                    $questions[$questionId]['answers'][$answer->getOpenAnswer()]++;
                                }
                                break;
                            default:
                                if(is_numeric($answer->getOpenAnswer())){
                                    $questions[$questionId]['answers'] +=  $answer->getOpenAnswer();
                                }
                        }
                    }

                }
            }
        }
        return $questions;
    }

    /**
     * @return Consultant[]|ArrayCollection
     */
    public function getConsultants()
    {
        return $this->consultants;
    }

    /**
     * @param Consultant[]|ArrayCollection $consultants
     * @return Campaign
     */
    public function setConsultants($consultants)
    {
        $this->consultants = $consultants;

        return $this;
    }


    public function setOpeningMessage(array $openingMessage) : self
    {
        $this->openingMessage = $openingMessage;

        return $this;
    }

    public function setOpeningMessageLocale($openingMessage, $locale) : self
    {
        $this->openingMessage[$locale] = $openingMessage;

        return $this;
    }

    public function getOpeningMessage(): array
    {
        return $this->openingMessage;
    }

    /**
     * @return array
     */
    public function getAdditionalQuestionsAvailableForClient(): array
    {
        return $this->additionalQuestionsAvailableForClient;
    }

    /**
     * @param array $additionalQuestionsAvailableForClient
     * @return Campaign
     */
    public function setAdditionalQuestionsAvailableForClient(array $additionalQuestionsAvailableForClient): Campaign
    {
        $this->additionalQuestionsAvailableForClient = $additionalQuestionsAvailableForClient;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalQuestionsAvailableForConsultant(): array
    {
        return $this->additionalQuestionsAvailableForConsultant;
    }

    /**
     * @param array $additionalQuestionsAvailableForConsultant
     * @return Campaign
     */
    public function setAdditionalQuestionsAvailableForConsultant(array $additionalQuestionsAvailableForConsultant): Campaign
    {
        $this->additionalQuestionsAvailableForConsultant = $additionalQuestionsAvailableForConsultant;

        return $this;
    }

    public function getQuestionsAvailableForConsultantVerbatimExport(): array
    {
        return $this->questionsAvailableForConsultantVerbatimExport;
    }

    public function setQuestionsAvailableForConsultantVerbatimExport(array $questionsAvailableForConsultantVerbatimExport): self
    {
        $this->questionsAvailableForConsultantVerbatimExport = $questionsAvailableForConsultantVerbatimExport;

        return $this;
    }

    public function isClientAreaAllowHouseView(): bool
    {
        return $this->clientAreaAllowHouseView;
    }

    public function setClientAreaAllowHouseView(bool $clientAreaAllowHouseView): self
    {
        $this->clientAreaAllowHouseView = $clientAreaAllowHouseView;

        return $this;
    }

    public function isConsultantAreaAllowHouseView(): bool
    {
        return $this->consultantAreaAllowHouseView;
    }

    public function setConsultantAreaAllowHouseView(bool $consultantAreaAllowHouseView): self
    {
        $this->consultantAreaAllowHouseView = $consultantAreaAllowHouseView;

        return $this;
    }

    public function isConsultantAreaAllowCartographyView(): bool
    {
        return $this->consultantAreaAllowCartographyView;
    }

    public function setConsultantAreaAllowCartographyView(bool $consultantAreaAllowCartographyView): self
    {
        $this->consultantAreaAllowCartographyView = $consultantAreaAllowCartographyView;

        return $this;
    }


    public function isClientAreaHouseAccessAllowed(): bool
    {
        // Only allow access after end date is in the past
        if($this->endDate && $this->endDate > new \DateTime()) {
            return false;
        }

        switch(true) {
            case $this->expectedAnswers === null:
                // When no expected answers number is set, allow as long as at least 6 participations are recorded
                return $this->getParticipationsCount() >= 6;
            case $this->expectedAnswers <= 5:
                // When expected answers are low, allow only when we have at least as many participations as expected
                return $this->getParticipationsCount() >= $this->expectedAnswers;
            default:
                // In other cases, allow when at least 25% (1/4th of expected answers) participations are recorded
                return $this->getParticipationsCount() >= $this->expectedAnswers / 4;
        }

    }

    public function getAnalysisVersion(): ?AnalysisVersion
    {
        return $this->analysisVersion;
    }

    public function setAnalysisVersion(AnalysisVersion $analysisVersion): self
    {
        $this->analysisVersion = $analysisVersion;

        return $this;
    }

    public function isAllowOtherGender(): bool
    {
        return $this->allowOtherGender;
    }

    public function setAllowOtherGender(bool $allowOtherGender): self
    {
        $this->allowOtherGender = $allowOtherGender;

        return $this;
    }

}
