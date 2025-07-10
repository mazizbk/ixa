<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="montgolfiere_campaign_segment_step")
 * @ORM\Entity()
 */
class CampaignSegmentStep
{
    public const TYPE_DIVIDER = 'divider';
    /** @var string Question related to an item */
    public const TYPE_ITEM = 'item';
    /** @var string Additional question */
    public const TYPE_QUESTION = 'question';

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $id;

    /**
     * @var CampaignSegment
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * @Gedmo\SortableGroup()
     */
    private $segment;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition()
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $position;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $type;

    /**
     * @var Theme|null
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $theme;

    /**
     * @var Item|null
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Item")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $item;

    /**
     * @var Question|null
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Question")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $question;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSegment(): CampaignSegment
    {
        return $this->segment;
    }

    public function setSegment(CampaignSegment $segment): self
    {
        if($this->segment) {
            $this->segment->removeStep($this);
        }

        $segment->addStep($this);
        $this->segment = $segment;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

}
