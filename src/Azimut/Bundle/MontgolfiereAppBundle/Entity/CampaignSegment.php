<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CampaignSegment
 *
 * @ORM\Table(name="montgolfiere_campaign_segment")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignSegmentRepository")
 */
class CampaignSegment
{
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
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", inversedBy="segments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $name;

    /**
     * @var Question[]|ArrayCollection<Question>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Question")
     * @ORM\JoinTable(name="montgolfiere_campaign_segment_question")
     * @Assert\Count(max="21")
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    private $questions;

    /**
     * @var CampaignParticipation[]|ArrayCollection<CampaignParticipation>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation", mappedBy="segment")
     */
    private $participations;

    /**
     * Stores questions that are after the 21 firsts
     * @var CampaignSegmentAdditionalQuestion[]|ArrayCollection<CampaignSegmentAdditionalQuestion>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentAdditionalQuestion", mappedBy="segment")
     * @ORM\OrderBy({"position": "ASC"})
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    private $additionalQuestions;

    /**
     * @var CampaignSegmentStep[]&ArrayCollection<CampaignSegmentStep>
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep", mappedBy="segment")
     * @Serializer\Groups({"backoffice_segments_list"})
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $steps;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $disabled = false;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     * @Assert\Choice(choices={"fr", "en"})
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $locale;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
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
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Question[]|ArrayCollection<Question>
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function getQuestions()
    {
        return new ArrayCollection($this->questions->toArray());
    }

    /**
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function addQuestion(Question $question)
    {
        foreach ($this->questions as $testedQuestion) {
            if ($testedQuestion->getItem() === $question->getItem()) {
                $this->questions->removeElement($testedQuestion);
                break;
            }
        }
        $this->questions->add($question);

        return $this;
    }

    /**
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function removeQuestion(Question $question)
    {
        $this->questions->removeElement($question);

        return $this;
    }

    /**
     * @return CampaignParticipation[]|ArrayCollection
     */
    public function getParticipations()
    {
        return new ArrayCollection($this->participations->toArray());
    }

    /**
     * @return bool
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    public function hasParticipations()
    {
        return !$this->participations->isEmpty();
    }

    public function isValid()
    {
        if($this->disabled) {
            return false;
        }
        foreach ($this->steps as $step) {
            if(($step->getType() === CampaignSegmentStep::TYPE_ITEM || $step->getType() === CampaignSegmentStep::TYPE_QUESTION) && !$step->getQuestion()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return CampaignSegmentAdditionalQuestion[]|ArrayCollection<CampaignSegmentAdditionalQuestion>
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function getAdditionalQuestions()
    {
        return new ArrayCollection($this->additionalQuestions->toArray());
    }

    /**
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function addAdditionalQuestion(CampaignSegmentAdditionalQuestion $additionalQuestion)
    {
        $this->additionalQuestions->add($additionalQuestion);

        return $this;
    }

    /**
     * @deprecated To be removed once all existing segments has been converted to the new Steps system
     */
    public function removeAdditionalQuestion(CampaignSegmentAdditionalQuestion $additionalQuestion)
    {
        $this->additionalQuestions->removeElement($additionalQuestion);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return CampaignSegmentStep[]|ArrayCollection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    public function addStep(CampaignSegmentStep $step): self
    {
        $this->steps[] = $step;

        return $this;
    }

    public function removeStep(CampaignSegmentStep $step): self
    {
        if(($i=array_search($step, $this->steps))!==false) {
            unset($this->steps[$i]);
        }

        return $this;
    }

    public function getStep($stepNumber): ?CampaignSegmentStep
    {
        foreach ($this->steps as $step) {
            if($step->getPosition() === $stepNumber) {
                return $step;
            }
        }

        return null;
    }

}
