<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-11-09 14:48:26
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_zone_filter")
 */
class ZoneFilter extends AbstractZoneFilter
{
    /**
     * @var ZoneDefinitionCmsFiles
     *
     * @ORM\ManyToOne(targetEntity="ZoneDefinitionCmsFiles", inversedBy="filters")
     */
    protected $zoneDefinition;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail_page_layout"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail_page_layout"})
     */
    private $label;

    public function __construct($property = null, $operation = null, $name = null, $label = null)
    {
        parent::__construct($property, $operation);
        $this->name = $name;
        $this->label = $label;
    }

    public function getName()
    {
        return $this->name ?: $this->property;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getLabel()
    {
        return $this->label ?: $this->getName();
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getQueryParameter(ParameterBag $requestQuery = null)
    {
        $value = $requestQuery->get($this->getName());

        if ($this->getOperation() == self::CONTAINS) {
            return '%'.$value.'%';
        } elseif ($this->getOperation() == self::BEGIN_WITH || $this->getOperation() == self::BEGIN_WITH_FIRST_LETTER) {
            return $value.'%';
        } elseif ($this->getOperation() == self::END_WITH) {
            return '%'.$value;
        }

        return $value;
    }
}
