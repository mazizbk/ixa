<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table("montgolfiere_restitution_item_table_text")
 */
class RestitutionItemTableText
{
    public const TREND_BAD_LEFT = 0;
    public const TREND_POOR_LEFT = 1;
    public const TREND_OK = 2;
    public const TREND_POOR_RIGHT = 3;
    public const TREND_BAD_RIGHT = 4;

    /**
     * @var Item|null
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Item", inversedBy="restitution")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id()
     */
    private $item;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     */
    private $trend;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $longSignification;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $shortSignification;

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getTrend(): ?int
    {
        return $this->trend;
    }

    public function setTrend(int $trend): self
    {
        $this->trend = $trend;

        return $this;
    }

    public function getLongSignification(): ?string
    {
        return $this->longSignification;
    }

    public function setLongSignification(?string $longSignification): self
    {
        $this->longSignification = $longSignification;

        return $this;
    }

    public function getShortSignification(): ?string
    {
        return $this->shortSignification;
    }

    public function setShortSignification(?string $shortSignification): self
    {
        $this->shortSignification = $shortSignification;

        return $this;
    }

}
