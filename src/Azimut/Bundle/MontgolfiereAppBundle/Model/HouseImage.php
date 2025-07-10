<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class HouseImage
{
    public const POSITION_TOP = 'top';
    public const POSITION_LEFT = 'left';

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $position;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $path;

    /**
     * @var HousePosition|null
     * @Serializer\Type("Azimut\Bundle\MontgolfiereAppBundle\Model\HousePosition")
     */
    private $offset;

    /**
     * @var float
     * @Serializer\Type("float")
     */
    private $scale = 1;

    public function __construct()
    {
        $this->offset = new HousePosition();
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOffset(): ?HousePosition
    {
        return $this->offset;
    }

    public function setOffset(?HousePosition $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getScale(): float
    {
        return $this->scale??1.0;
    }

    public function setScale(?float $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

}
