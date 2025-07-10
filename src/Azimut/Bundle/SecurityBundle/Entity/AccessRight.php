<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-07 14:57:04
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\VirtualProperty;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="type", type="string")
* @ORM\Table(name="security_access_right")
*
* @DynamicInheritanceMap
*
* @ORM\Entity(repositoryClass="Azimut\Bundle\SecurityBundle\Entity\Repository\AccessRightRepository")
*/
class AccessRight
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    protected $id;

    /**
     * @var AccessRole[]|ArrayCollection<AccessRole>
     *
     * @ORM\ManyToMany(targetEntity="AccessRole", inversedBy="accessRights", cascade={"persist"})
     * @ORM\JoinTable(name="security_access_right_access_role")
     * @Groups({"detail_access_right", "list_access_rights"})
     * @Assert\Valid()
     */
    protected $roles;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessRights")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     * @Groups({"detail_access_right"})
     *
     */
    private $user;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="accessRights")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="cascade")
     * @Groups({"detail_access_right"})
     */
    private $group;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getFormType()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\', '\\Form\\Type\\', $type);
        $type.= 'Type';

        return $type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Check if a role is supported from this access right,
     * @param $role string
     */
    public function hasRole($role)
    {
        foreach ($this->getRoles() as $arRole) {
            if ($arRole->getRole() == $role) {
                return true;
            }
        }
        return false;
    }

    public function addRole(AccessRole $accessRole)
    {
        if (!$this->hasRole($accessRole->getRole())) {
            $this->getRoles()->add($accessRole);
        }

        return $this;
    }

    public function removeRole(AccessRole $arRole)
    {
        $this->getRoles()->removeElement($arRole);
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setUser(User $user)
    {
        if ($this->getGroup() == null) {
            if ($this->user !== $user) {
                $this->user = $user;
            }
        } else {
            throw new \RuntimeException(sprintf('AccessRight already used for a group.'));
        }
    }

    public function setGroup(Group $group)
    {
        if ($this->getUser() == null) {
            if ($this->group !== $group) {
                $this->group = $group;
            }
        } else {
            throw new \RuntimeException(sprintf('AccessRight already used for a user.'));
        }
    }

    /**
     * Check if a user is supported from this access right,
     * Used from Voter.
     * @param $user User
     */
    public function hasUser(User $user)
    {
        return $this->getUser() === $user;
    }

    public function hasGroup(Group $group)
    {
        return  $this->getGroup() === $group;
    }

    public function removeUser(User $user)
    {
        if ($this->getUser() != null && $this->getUser() === $user) {
            $this->user = null;

            return true;
        }

        return false;
    }

    public function removeGroup(Group $group)
    {
        if ($this->getGroup() != null && $this->getGroup() === $group) {
            $this->group = null;

            return true;
        }

        return false;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    public function getAccessRightType()
    {
        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getUserId()
    {
        if (null === $this->getUser()) {
            return null;
        }

        return $this->getUser()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getGroupId()
    {
        if (null === $this->getGroup()) {
            return null;
        }

        return $this->getGroup()->getId();
    }

    public function getObject()
    {
        return null;
    }

}
