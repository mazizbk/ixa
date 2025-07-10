<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 10:56:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractZoneFilter
{
    const CONTAINS = 0;
    const EQUALS = 1;
    const BEGIN_WITH = 2;
    const BEGIN_WITH_FIRST_LETTER = 3;
    const END_WITH = 4;
    const GREATER_THAN = 5;
    const LOWER_THAN = 6;
    const GREATER_THAN_OR_EQUALS = 7;
    const LOWER_THAN_OR_EQUALS = 8;
    const GREATER_THAN_OR_NULL = 9;
    const LOWER_THAN_OR_NULL = 10;
    const GREATER_THAN_OR_EQUALS_OR_NULL = 11;
    const LOWER_THAN_OR_EQUALS_OR_NULL = 12;
    const IS_NOT_NULL = 13;
    const IN = 14;
    const NOT_EQUALS = 15;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_page_layout"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail_page_layout"})
     */
    protected $property;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"detail_page_layout"})
     */
    protected $operation;

    protected $zoneDefinition;

    public function __construct($property = null, $operation = null)
    {
        $this->property = $property;
        $this->operation = $operation;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    public function getOperation()
    {
        return $this->operation;
    }
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    public function getZoneDefinition()
    {
        return $this->zoneDefinition;
    }

    public function setZoneDefinition(ZoneDefinitionCmsFiles $zoneDefinition)
    {
        $this->zoneDefinition = $zoneDefinition;
        return $this;
    }

    public function getQueryParameterName()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $shortName = (new \ReflectionClass($this))->getShortName();
        return str_replace($this->property, '.', '_').'_'.$shortName.'_'.'_'.$this->id;
    }

    public function getQuery($entityAliasName)
    {
        if ($this->getOperation() == self::EQUALS) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' = :'.$this->getQueryParameterName();
        } elseif ($this->getOperation() == self::GREATER_THAN) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' > :'.$this->getQueryParameterName();
        } elseif ($this->getOperation() == self::LOWER_THAN) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' < :'.$this->getQueryParameterName();
        } elseif ($this->getOperation() == self::GREATER_THAN_OR_NULL) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' > :'.$this->getQueryParameterName().' OR '.$entityAliasName.'.'.$this->getResolvedProperty().' IS NULL';
        } elseif ($this->getOperation() == self::LOWER_THAN_OR_NULL) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' < :'.$this->getQueryParameterName().' OR '.$entityAliasName.'.'.$this->getResolvedProperty().' IS NULL';
        } elseif ($this->getOperation() == self::GREATER_THAN_OR_EQUALS) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' >= :'.$this->getQueryParameterName();
        } elseif ($this->getOperation() == self::LOWER_THAN_OR_EQUALS) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' <= :'.$this->getQueryParameterName();
        } elseif ($this->getOperation() == self::GREATER_THAN_OR_EQUALS_OR_NULL) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' >= :'.$this->getQueryParameterName().' OR '.$entityAliasName.'.'.$this->getResolvedProperty().' IS NULL';
        } elseif ($this->getOperation() == self::LOWER_THAN_OR_EQUALS_OR_NULL) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' <= :'.$this->getQueryParameterName().' OR '.$entityAliasName.'.'.$this->getResolvedProperty().' IS NULL';
        } elseif ($this->getOperation() == self::IS_NOT_NULL) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' IS NOT NULL OR :'.$this->getQueryParameterName().' IS NULL';
        } elseif ($this->getOperation() == self::IN) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' IN (:'.$this->getQueryParameterName().')';
        } elseif ($this->getOperation() == self::NOT_EQUALS) {
            return $entityAliasName.'.'.$this->getResolvedProperty().' IS NULL OR '. $entityAliasName.'.'.$this->getResolvedProperty().' != :'.$this->getQueryParameterName();
        }

        return $entityAliasName.'.'.$this->getResolvedProperty().' LIKE :'.$this->getQueryParameterName();
    }

    /**
     * Build operation choices (for form types)
     * @return array
     */
    public static function buildOperationsChoices()
    {
        return [
            'contains'                       => self::CONTAINS,
            'equals'                         => self::EQUALS,
            'begin with'                     => self::BEGIN_WITH,
            'begin with first letter'        => self::BEGIN_WITH_FIRST_LETTER,
            'end with'                       => self::END_WITH,
            'greater than'                   => self::GREATER_THAN,
            'lower than'                     => self::LOWER_THAN,
            'greater than or equals'         => self::GREATER_THAN_OR_EQUALS,
            'lower than or equals'           => self::LOWER_THAN_OR_EQUALS,
            'greater than or null'           => self::GREATER_THAN_OR_NULL,
            'lower than or null'             => self::LOWER_THAN_OR_NULL,
            'greater than or equals or null' => self::GREATER_THAN_OR_EQUALS_OR_NULL,
            'lower than or equals or null'   => self::LOWER_THAN_OR_EQUALS_OR_NULL,
            'is not null'                    => self::IS_NOT_NULL,
            'in'                             => self::IN,
            'not equals'                     => self::NOT_EQUALS,
        ];
    }

    /**
     * @param ParameterBag|null $requestQuery
     */
    abstract public function getQueryParameter(ParameterBag $requestQuery = null);

    /**
     * Return the property without association name
     * Ex : if property is 'comments.rating', it will return 'rating'
     */
    protected function getResolvedProperty()
    {
        $property = $this->property;

        // Keep only last property name if property holds association
        if (false !== $pointIndex = strrpos($property, '.')) {
            $property = substr($property, $pointIndex + 1);
        }

        return $property;
    }
}
