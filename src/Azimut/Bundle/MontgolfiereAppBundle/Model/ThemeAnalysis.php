<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItem;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use JMS\Serializer\Annotation as Serializer;

class ThemeAnalysis extends Analysis
{
    /**
     * @var ItemAnalysis[]
     */
    private $items = [];

    /**
     * @var RestitutionItem|null
     */
    private $restitution;

    /**
     * @var WordSettings|null
     * @Serializer\Exclude()
     */
    private $wordSettings;

    /**
     * @var Theme
     * @Serializer\Exclude()
     */
    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        parent::__construct();
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    /**
     * @return ItemAnalysis[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getItem(Item $item): ItemAnalysis
    {
        foreach ($this->items as $itemAnalysis) {
            if($itemAnalysis->getItem() === $item) {
                return $itemAnalysis;
            }
        }
        throw new \LogicException('Item '.$item->getId().' has not been analyzed');
    }

    public function addItem(ItemAnalysis $analysis): self
    {
        $this->preventLockedEdit();

        $this->items[] = $analysis;

        return $this;
    }

    public function getRestitution(): ?RestitutionItem
    {
        return $this->restitution;
    }

    public function setRestitution(?RestitutionItem $restitution): self
    {
        $this->preventLockedEdit();
        $this->restitution = $restitution;

        return $this;
    }

    /**
     * Transforms the theme analysis into an item analysis
     * This is used by virtual themes which are analysis of other themes (instead having their items)
     * @return VirtualItemAnalysis
     */
    public function asItemAnalysis(): VirtualItemAnalysis
    {
        $result = new VirtualItemAnalysis();
        $result
            ->setName($this->getName())
            ->setWorkcareAverage($this->getWorkcareAverage())
            ->setAnalysisVersion($this->getAnalysisVersion())
        ;

        return $result;
    }

    public function getWordSettings(): ?WordSettings
    {
        return $this->wordSettings;
    }

    public function setWordSettings(?WordSettings $wordSettings): self
    {
        $this->wordSettings = $wordSettings;

        return $this;
    }

}
