<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-31 16:38:40
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\ZoneDefinitionRepository")
 * @ORM\Table(name="frontoffice_zone_definition")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"simple"="ZoneDefinition", "cmsfiles"="ZoneDefinitionCmsFiles", "form"="ZoneDefinitionForm", "cmsfile_form"="ZoneDefinitionCmsFileBufferForm"})
 */
class ZoneDefinition
{
    const ZONE_DEFINITION_TYPE = 'simple';

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"detail_page_layout"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     * @Groups({"detail_page_layout"})
     */
    private $name;

    /**
     * @var PageLayout
     *
     * @ORM\ManyToOne(targetEntity="PageLayout", inversedBy="zoneDefinitions")
     * @ORM\JoinColumn(name="layout_id")
     */
    private $layout;

    public function __construct($name = null, $options = null)
    {
        $this->setName($name);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout(PageLayout $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page_layout"})
     */
    public static function getZoneDefinitionType()
    {
        return static::ZONE_DEFINITION_TYPE;
    }
}
