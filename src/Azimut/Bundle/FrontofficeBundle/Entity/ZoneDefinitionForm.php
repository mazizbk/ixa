<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-28 11:24:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 */
class ZoneDefinitionForm extends ZoneDefinition
{
    const ZONE_DEFINITION_TYPE = 'form';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail_page_layout"})
     */
    private $controller;

    public function __construct($name = null, $options = null)
    {
        parent::__construct($name, $options);

        if (isset($options['controller'])) {
            $this->setController($options['controller']);
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }
}
