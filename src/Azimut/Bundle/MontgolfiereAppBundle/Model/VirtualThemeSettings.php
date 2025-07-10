<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class VirtualThemeSettings
{
    /**
     * @var int[]
     * @Serializer\Type("array<int>")
     */
    private $parentThemesIds;

    /**
     * @return int[]
     */
    public function getParentThemesIds(): ?array
    {
        return $this->parentThemesIds;
    }

    /**
     * @param int[] $parentThemesIds
     * @return $this
     */
    public function setParentThemesIds(array $parentThemesIds): self
    {
        $this->parentThemesIds = $parentThemesIds;

        return $this;
    }

}
