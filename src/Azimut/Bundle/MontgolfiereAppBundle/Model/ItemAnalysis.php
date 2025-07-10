<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use JMS\Serializer\Annotation as Serializer;

class ItemAnalysis extends Analysis
{
    /**
     * @var Theme|null
     * @Serializer\Exclude()
     */
    private $theme;

    /**
     * @var Item|null
     * @Serializer\Exclude()
     */
    private $item;

    /**
     * @var string|null
     */
    private $shortSignification;

    /**
     * @var string|null
     */
    private $longSignification;

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

    public function getShortSignification(): ?string
    {
        return $this->shortSignification;
    }

    public function setShortSignification(?string $shortSignification): self
    {
        $this->shortSignification = $shortSignification;

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

}
