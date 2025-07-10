<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 11:33:53
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_zone_permanent_filter")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class ZonePermanentFilter extends AbstractZoneFilter
{
    /**
     * @var ZoneDefinitionCmsFiles
     *
     * @ORM\ManyToOne(targetEntity="ZoneDefinitionCmsFiles", inversedBy="permanentFilters")
     */
    protected $zoneDefinition;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"detail_page_layout"})
     */
    protected $value;

    public function __construct($property = null, $operation = null, $value = null)
    {
        parent::__construct($property, $operation);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getComputedValue()
    {
        return $this->value;
    }

    public function getQueryParameter(ParameterBag $requestQuery = null)
    {
        $value = $this->getComputedValue();

        if ($this->getOperation() == self::CONTAINS) {
            return '%'.$value.'%';
        } elseif ($this->getOperation() == self::BEGIN_WITH || $this->getOperation() == self::BEGIN_WITH_FIRST_LETTER) {
            return $value.'%';
        } elseif ($this->getOperation() == self::END_WITH) {
            return '%'.$value;
        }

        return $value;
    }

    /**
    * @VirtualProperty
    * @Groups({"detail_page_layout"})
    */
    public function getClass()
    {
        return get_class($this);
    }
}
