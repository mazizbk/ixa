<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * CampaignParticipationAnswer
 *
 * @ORM\Table(name="montgolfiere_campaign_participation_answer", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_answer", columns={"participation_id", "step_id"})
 * })
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignParticipationAnswerRepository")
 */
class CampaignParticipationAnswer
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
     * @var CampaignParticipation
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $participation;

    /**
     * @var CampaignSegmentStep|null
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $step;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer", nullable=true)
     * @Assert\Range(min="-10", max="10")
     */
    private $value;

    /**
     * @var string|string[]
     *
     * @ORM\Column(name="openAnswer", type="object", nullable=true)
     */
    private $openAnswer;

    /**
     * @var bool
     *
     * @ORM\Column(name="skipped", type="boolean", nullable=true)
     */
    private $skipped;

    /**
     * Stores the question for validation purpose. This is not available once the entity is persisted
     * @var Question
     */
    private $question;

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
     * Set participation
     *
     * @param CampaignParticipation $participation
     *
     * @return $this
     */
    public function setParticipation(CampaignParticipation $participation)
    {
        $this->participation = $participation;

        return $this;
    }

    /**
     * Get participation
     *
     * @return CampaignParticipation
     */
    public function getParticipation()
    {
        return $this->participation;
    }

    public function getStep(): ?CampaignSegmentStep
    {
        return $this->step;
    }

    public function setStep(?CampaignSegmentStep $step): self
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set openAnswer
     *
     * @param string|string[] $openAnswer
     *
     * @return $this
     */
    public function setOpenAnswer($openAnswer)
    {
        $this->openAnswer = $openAnswer;

        return $this;
    }

    /**
     * Get openAnswer
     *
     * @return string|string[]
     */
    public function getOpenAnswer()
    {
        return $this->openAnswer;
    }

    /**
     * Set skipped
     *
     * @param boolean $skipped
     *
     * @return $this
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;

        return $this;
    }

    /**
     * Get skipped
     *
     * @return bool
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Question $question
     * @return $this
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @Assert\Callback()
     */
    public function isValid(ExecutionContextInterface $context, $payload)
    {
        if(!$this->question) {
            return;
        }
        if($this->question->getCanBeSkipped() && $this->skipped) {
            return;
        }

        if($this->question->getType() === Question::TYPE_SLIDER_VALUE && is_null($this->value)) {
            $context->buildViolation('This value should not be blank.')
                    ->atPath('value')
                    ->addViolation()
            ;
        }

        if($this->question->getType() !== Question::TYPE_SLIDER_VALUE && $this->question->getType() !== Question::TYPE_SATISFACTION_GAUGE && !$this->openAnswer) {
            $context->buildViolation('This value should not be blank.')
                ->atPath('openAnswer')
                ->addViolation()
            ;
        }
    }
}

