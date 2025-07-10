<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class HouseDimension
{
    /**
     * @var int
     * @Serializer\Type("integer")
     */
    private $width = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     */
    private $height = 0;

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

}
