<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use JMS\Serializer\Annotation as Serializer;

class WordSettings
{
    /**
     * @var int|null
     * @Serializer\Type("integer")
     */
    private $row;

    /**
     * @var int|null
     * @Serializer\Type("integer")
     */
    private $column;

    /**
     * @var bool|null
     * @Serializer\Type("boolean")
     */
    private $skipInItemRestitutionTable = false;

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(?int $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getColumn(): ?int
    {
        return $this->column;
    }

    public function setColumn(?int $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function isSkipInItemRestitutionTable(): bool
    {
        return $this->skipInItemRestitutionTable;
    }

    public function setSkipInItemRestitutionTable(bool $skipInItemRestitutionTable): self
    {
        $this->skipInItemRestitutionTable = $skipInItemRestitutionTable;

        return $this;
    }

}
