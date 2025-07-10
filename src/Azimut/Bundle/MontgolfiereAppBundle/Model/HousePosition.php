<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class HousePosition
{
    /**
     * @var int
     * @Serializer\Type("integer")
     */
    private $x;

    /**
     * @var int
     * @Serializer\Type("integer")
     */
    private $y;

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

}
