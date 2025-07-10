<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-07 14:56:36
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

/**
* @ORM\Entity(repositoryClass="Azimut\Bundle\SecurityBundle\Entity\Repository\AccessRoleRepository")
* @ORM\Table(name="security_access_role")
*/
class AccessRole
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"role", "detail_access_right", "list_access_rights"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     * @Groups({"role", "detail_access_right"})
     */
    protected $role;

    /**
     * @var string
     *
     * @Groups({"role", "detail_access_right", "list_access_rights"})
     */
    private $name;

    /**
     * @var AccessRight[]|ArrayCollection<AccessRight>
     *
     * @ORM\ManyToMany(targetEntity="AccessRight", mappedBy="roles")
     */
    protected $accessRights;

    public function __construct()
    {
        $this->accessRights = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function getAccessRight()
    {
        return $this->accessRights;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function toArray()
    {
        $array['id'] = $this->getId();
        $array['role'] = $this->getRole();
        return $array;
    }
}
