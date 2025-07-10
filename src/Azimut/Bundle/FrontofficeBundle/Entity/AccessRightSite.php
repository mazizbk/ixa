<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2013-09-25
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_access_right_site")
 * @DynamicInheritanceSubClass(discriminatorValue="site")
 */
class AccessRightSite extends AccessRight
{
    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\Site", inversedBy="accessRights")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $site;

    public function getSite()
    {
        return $this->site;
    }

    public function setSite(Site $site)
    {
        $this->site = $site;
        $site->addAccessRight($this);

        return $this;
    }

    /**
     * @return Site
     */
    public function getObject()
    {
        return $this->site;
    }

    public function setObject($site)
    {
        return $this->site = $site;
    }

    public static function getObjectClass()
    {
        return Site::class;
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
        return 'site';
    }
}
