<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-09-25
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_access_right_page")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="page")
 */
class AccessRightPage extends AccessRight
{
    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\Page", inversedBy="accessRights")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $page;

    public function getPage()
    {
        return $this->page;
    }

    public function setPage(Page $page)
    {
        $this->page = $page;
        $page->addAccessRight($this);

        return $this;
    }

    /**
     * @return Page
     */
    public function getObject()
    {
        return $this->page;
    }

    public function setObject($page)
    {
        return $this->page = $page;
    }

    public static function getObjectClass()
    {
        return Page::class;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getObjectId()
    {
        if (null === $this->getObject()) {
            return null;
        }

        return $this->getObject()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    public function getAccessRightType()
    {
        return 'page';
    }
}
