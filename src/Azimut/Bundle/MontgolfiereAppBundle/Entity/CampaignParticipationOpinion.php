<?php
/**
 * User: goulven
 * Date: 08/08/2022
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * CampaignParticipationOpinion
 * @ORM\Table(name="montgolfiere_campaign_participation_opinion")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignParticipationOpinionRepository")
 */
class CampaignParticipationOpinion
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $id;

    /**
     * @var CampaignParticipation
     *
     * @ORM\OneToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation", inversedBy="opinion")
     * @ORM\JoinColumn(nullable=false, unique=true, onDelete="CASCADE")
     */
    private $participation;

    /**
     * @var string
     *
     * @ORM\Column(name="question1", type="string", length=255, nullable=true)
     */
    private $question1;

    /**
     * @var string
     *
     * @ORM\Column(name="question2", type="text", nullable=true)
     */
    private $question2;

    /**
     * @var string
     *
     * @ORM\Column(name="question3", type="string", length=255, nullable=true)
     */
    private $question3;

    /**
     * @var string
     *
     * @ORM\Column(name="question4", type="text", nullable=true)
     */
    private $question4;

    /**
     * @var string
     *
     * @ORM\Column(name="question5", type="string", length=255, nullable=true)
     */
    private $question5;

    /**
     * @var string
     *
     * @ORM\Column(name="question6", type="text", nullable=true)
     */
    private $question6;

    /**
     * @var string
     *
     * @ORM\Column(name="question7", type="text", nullable=true)
     */
    private $question7;

    /**
     * @var string
     *
     * @ORM\Column(name="question8", type="string", length=255, nullable=true)
     */
    private $question8;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CampaignParticipationOpinion
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getParticipation(): CampaignParticipation
    {
        return $this->participation;
    }

    public function setParticipation(CampaignParticipation $participation): self
    {
        $this->participation = $participation;

        return $this;
    }

    public function getQuestion1(): ?string
    {
        return $this->question1;
    }

    public function setQuestion1(?string $question1): self
    {
        $this->question1 = $question1;

        return $this;
    }

    public function getQuestion2(): ?string
    {
        return $this->question2;
    }

    public function setQuestion2(?string $question2): self
    {
        $this->question2 = $question2;

        return $this;
    }

    public function getQuestion3(): ?string
    {
        return $this->question3;
    }

    public function setQuestion3(?string $question3): self
    {
        $this->question3 = $question3;

        return $this;
    }

    public function getQuestion4(): ?string
    {
        return $this->question4;
    }

    public function setQuestion4(?string $question4): self
    {
        $this->question4 = $question4;

        return $this;
    }

    public function getQuestion5(): ?string
    {
        return $this->question5;
    }

    public function setQuestion5(?string $question5): self
    {
        $this->question5 = $question5;

        return $this;
    }

    public function getQuestion6(): ?string
    {
        return $this->question6;
    }

    public function setQuestion6(?string $question6): self
    {
        $this->question6 = $question6;

        return $this;
    }

    public function getQuestion7(): ?string
    {
        return $this->question7;
    }

    public function setQuestion7(?string $question7): self
    {
        $this->question7 = $question7;

        return $this;
    }

    public function getQuestion8(): ?string
    {
        return $this->question8;
    }

    public function setQuestion8(?string $question8): self
    {
        $this->question8 = $question8;

        return $this;
    }
}