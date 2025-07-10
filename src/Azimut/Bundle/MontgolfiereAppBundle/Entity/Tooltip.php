<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="montgolfiere_tooltip")
 */
class Tooltip
{
    /**
     * @var Item
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Item", inversedBy="tooltips")
     */
    protected $item;

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(type="string", length=5)
     */
    protected $locale;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=false)
     */
    protected $text;

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function __clone()
    {
        $this->item = null;
    }

}
