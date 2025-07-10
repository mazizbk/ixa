<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Question
 *
 * @ORM\Table(name="montgolfiere_question")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\QuestionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\SoftDeleteable(fieldName="archivedAt")
 */
class Question
{

    const TYPE_SLIDER_VALUE = 0,
        TYPE_OPEN = 1,
        TYPE_TRUE_FALSE = 2,
        TYPE_CHOICES_MULTIPLES = 3,
        TYPE_CHOICES_UNIQUE = 4,
        TYPE_SATISFACTION_GAUGE = 5
    ;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="question", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $description;

    /**
     * @var Item|null
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Item", inversedBy="questions")
     * @ORM\JoinColumn(nullable=true, onDelete="RESTRICT")
     */
    private $item;

    /**
     * @var array
     *
     * @ORM\Column(name="valuesDistribution", type="nullable_simple_array", nullable=true)
     * @Assert\Count(min="20", max="20")
     * @Assert\All({
     *     @Assert\Range(min=0, max=1000)
     * })
     */
    private $valuesDistribution;

    /**
     * @var string
     *
     * @ORM\Column(name="leftLabel", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $leftLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="centerLabel", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $centerLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="rightLabel", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $rightLabel;

    /**
     * @var bool
     *
     * @ORM\Column(name="canBeSkipped", type="boolean")
     */
    private $canBeSkipped;

    /**
     * @var QuestionTag[]|ArrayCollection<QuestionTag>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\QuestionTag", fetch="EAGER")
     * @ORM\JoinTable(name="montgolfiere_question_tags")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $tags;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\Choice(choices={
     *     Question::TYPE_SLIDER_VALUE,
     *     Question::TYPE_OPEN,
     *     Question::TYPE_TRUE_FALSE,
     *     Question::TYPE_CHOICES_MULTIPLES,
     *     Question::TYPE_CHOICES_UNIQUE,
     *     Question::TYPE_SATISFACTION_GAUGE
     * })
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $possibleValues;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="1")
     */
    private $gaugeMaxValue;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0")
     */
    private $wellBeingCoefficient;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0")
     */
    private $engagementCoefficient;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $gaugeInvert = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $tooltip;

    /**
     * @var AnalysisVersion
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $analysisVersion;

    public function __construct()
    {
        $this->setDefaultValuesDistribution();
        $this->tags = new ArrayCollection();
    }

    protected function setDefaultValuesDistribution()
    {
        $this->valuesDistribution = [];
        for($i=0;$i<20;$i++) {
            $this->valuesDistribution[$i] = 50*($i+1);
        }
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
     * @param int $id
     * @return Question
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set question
     *
     * @param string $question
     *
     * @return Question
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Question
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Set valuesDistribution
     *
     * @param array $valuesDistribution
     *
     * @return Question
     */
    public function setValuesDistribution($valuesDistribution)
    {
        $this->valuesDistribution = $valuesDistribution;
        sort($this->valuesDistribution);

        return $this;
    }

    /**
     * Get valuesDistribution
     *
     * @return array
     */
    public function getValuesDistribution()
    {
        if(null === $this->valuesDistribution) {
            $this->setDefaultValuesDistribution();
        }
        return $this->valuesDistribution;
    }

    /**
     * Set leftLabel
     *
     * @param string $leftLabel
     *
     * @return Question
     */
    public function setLeftLabel($leftLabel)
    {
        $this->leftLabel = $leftLabel;

        return $this;
    }

    /**
     * Get leftLabel
     *
     * @return string
     */
    public function getLeftLabel()
    {
        return $this->leftLabel;
    }

    /**
     * Set centerLabel
     *
     * @param string $centerLabel
     *
     * @return Question
     */
    public function setCenterLabel($centerLabel)
    {
        $this->centerLabel = $centerLabel;

        return $this;
    }

    /**
     * Get centerLabel
     *
     * @return string
     */
    public function getCenterLabel()
    {
        return $this->centerLabel;
    }

    /**
     * Set rightLabel
     *
     * @param string $rightLabel
     *
     * @return Question
     */
    public function setRightLabel($rightLabel)
    {
        $this->rightLabel = $rightLabel;

        return $this;
    }

    /**
     * Get rightLabel
     *
     * @return string
     */
    public function getRightLabel()
    {
        return $this->rightLabel;
    }

    /**
     * Set canBeSkipped
     *
     * @param boolean $canBeSkipped
     *
     * @return Question
     */
    public function setCanBeSkipped($canBeSkipped)
    {
        $this->canBeSkipped = $canBeSkipped;

        return $this;
    }

    /**
     * Get canBeSkipped
     *
     * @return bool
     */
    public function getCanBeSkipped()
    {
        return $this->canBeSkipped;
    }

    /**
     * @return QuestionTag[]|ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(QuestionTag $questionTag)
    {
        if(!$this->tags->contains($questionTag)) {
            $this->tags->add($questionTag);
        }

        return $this;
    }

    public function removeTag(QuestionTag $questionTag)
    {
        $this->tags->removeElement($questionTag);

        return $this;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function removeDistributionIfOpenQuestion()
    {
        if($this->type === self::TYPE_SLIDER_VALUE) {
            return;
        }

        $this->valuesDistribution = null;
        $this->leftLabel = null;
        $this->centerLabel = null;
        $this->rightLabel = null;
    }

    /**
     * @Assert\Callback()
     * @param ExecutionContextInterface $context
     */
    public function validateQuestionType(ExecutionContextInterface $context)
    {
        switch($this->type) {
            case self::TYPE_SLIDER_VALUE:
                $requiredFields = ['item', 'valuesDistribution'];
                break;
            case self::TYPE_CHOICES_MULTIPLES:
            case self::TYPE_CHOICES_UNIQUE:
                $requiredFields = ['possibleValues'];
                break;
            case self::TYPE_SATISFACTION_GAUGE:
                $requiredFields = ['gaugeMaxValue'];
                break;
            default:
                return;
        }

        foreach ($requiredFields as $field) {
            if(is_null($this->$field)) {
                $context->buildViolation('This value should not be null.')
                    ->atPath($field)
                    ->addViolation()
                ;
            }
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param \DateTime|null $archivedAt
     * @return $this
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getPossibleValues()
    {
        return $this->possibleValues;
    }

    /**
     * @param string $possibleValues
     * @return $this
     */
    public function setPossibleValues($possibleValues)
    {
        $this->possibleValues = $possibleValues;

        return $this;
    }

    /**
     * @return int
     */
    public function getGaugeMaxValue()
    {
        return $this->gaugeMaxValue;
    }

    /**
     * @param int $gaugeMaxValue
     * @return $this
     */
    public function setGaugeMaxValue($gaugeMaxValue)
    {
        $this->gaugeMaxValue = $gaugeMaxValue;

        return $this;
    }

    public function getWellBeingCoefficient(): ?int
    {
        return $this->wellBeingCoefficient;
    }

    public function setWellBeingCoefficient(?int $wellBeingCoefficient): self
    {
        $this->wellBeingCoefficient = $wellBeingCoefficient;

        return $this;
    }

    public function getEngagementCoefficient(): ?int
    {
        return $this->engagementCoefficient;
    }

    public function setEngagementCoefficient(?int $engagementCoefficient): self
    {
        $this->engagementCoefficient = $engagementCoefficient;

        return $this;
    }

    public function isGaugeInvert(): bool
    {
        return $this->gaugeInvert;
    }

    public function setGaugeInvert(bool $gaugeInvert): self
    {
        $this->gaugeInvert = $gaugeInvert;

        return $this;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function setTooltip(?string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
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

    public function __clone()
    {
        $this->id = null;
    }

}
