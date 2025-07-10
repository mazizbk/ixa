<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class HouseSettings
{
    public const TYPE_THEME = 'theme';
    public const TYPE_ITEM = 'item';
    public const ARROW_DIRECTION_DOWN = 'down';
    public const ARROW_DIRECTION_LEFT = 'left';
    public const ARROW_DIRECTION_RIGHT = 'right';

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * @var HousePosition|null
     * @Serializer\Type("Azimut\Bundle\MontgolfiereAppBundle\Model\HousePosition")
     */
    private $position;

    /**
     * @var HouseDimension|null
     * @Serializer\Type("Azimut\Bundle\MontgolfiereAppBundle\Model\HouseDimension")
     */
    private $dimension;

    /**
     * @var bool
     * @Serializer\Type("bool")
     */
    private $isRoof = false;

    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $arrowDirection;

    /**
     * @var int|null
     * @Serializer\Type("integer")
     */
    private $arrowLineLength;

    /**
     * @var int|null
     * @Serializer\Type("integer")
     */
    private $arrowLineOffset;

    /**
     * @var HouseImage
     * @Serializer\Type("Azimut\Bundle\MontgolfiereAppBundle\Model\HouseImage")
     */
    private $image;

    public function __construct()
    {
        $this->position = new HousePosition();
        $this->dimension = new HouseDimension();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPosition(): HousePosition
    {
        return $this->position;
    }

    public function setPosition(HousePosition $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDimension(): ?HouseDimension
    {
        return $this->dimension;
    }

    public function setDimension(HouseDimension $dimension): self
    {
        $this->dimension = $dimension;

        return $this;
    }

    public function isRoof(): bool
    {
        return $this->isRoof;
    }

    public function setIsRoof(bool $isRoof): self
    {
        $this->isRoof = $isRoof;

        return $this;
    }

    public function getArrowDirection(): ?string
    {
        return $this->arrowDirection;
    }

    public function setArrowDirection(?string $arrowDirection): self
    {
        $this->arrowDirection = $arrowDirection;

        return $this;
    }

    public function getArrowLineLength(): ?int
    {
        return $this->arrowLineLength;
    }

    public function setArrowLineLength(?int $arrowLineLength): self
    {
        $this->arrowLineLength = $arrowLineLength;

        return $this;
    }

    public function getArrowLineOffset(): ?int
    {
        return $this->arrowLineOffset;
    }

    public function setArrowLineOffset(?int $arrowLineOffset): self
    {
        $this->arrowLineOffset = $arrowLineOffset;

        return $this;
    }

    public function getImage(): ?HouseImage
    {
        return $this->image;
    }

    public function setImage(HouseImage $image): self
    {
        $this->image = $image;

        return $this;
    }

}
