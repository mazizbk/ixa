<?php
/**
* @author: Gerda Le Duc <gerda.leduc@azimut.net>
* date:   2013-11-07 14:57:26
*/

/**
* Group Class
*/
namespace Azimut\Bundle\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

/**
* @ORM\Entity
* @ORM\Table(name="security_group")
*/
class Group
{
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_group", "detail_group", "list_user", "detail_user", "detail_access_right", "list_access_rights"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60)
     * @Groups({"list_group", "detail_group", "detail_user", "detail_access_right", "list_access_rights"})
     */
    protected $name;

    /**
     * @var User[]|ArrayCollection<User>
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     */
    private $users;

    /**
     * @var AccessRight[]|ArrayCollection<AccessRight>
     *
     * @ORM\OneToMany(targetEntity="AccessRight", orphanRemoval=true, cascade={"persist"}, mappedBy="group")
     * @Groups({"detail_group"})
     */
    protected $accessRights;

    public function __construct()
    {
        //$this->name = $name;
        $this->users = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getUsers()
    {
        return $this->users ?: $this->users = new ArrayCollection();
    }

    public function getUsernames()
    {
        $names = array();
        foreach ($this->getUsers() as $user) {
            $names[] = $user->getUsername();
        }

        return $names;
    }

    public function hasUser($name)
    {
        return in_array($name, $this->getUsernames());
    }

    public function addUser(User $user)
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
            $user->addGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user)
    {
        if ($this->getUsers()->contains($user)) {
            $this->getUsers()->removeElement($user);
            $user->removeGroup($this);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * @return ArrayCollection|AccessRight[]
     */
    public function getAccessRights()
    {
        return $this->accessRights;
    }

    public function addAccessRight(AccessRight $ar)
    {
        if (!$this->getAccessRights()->contains($ar)) {
            $this->getAccessRights()->add($ar);
            $ar->setGroup($this);
        }

        return $this;
    }

    public function removeAccessRight(AccessRight $ar)
    {
        if ($this->getAccessRights()->contains($ar)) {
            $this->getAccessRights()->removeElement($ar);
            $ar->removeGroup($this);
        }

        return $this;
    }

    public function hasAccessRights()
    {
        return !$this->getAccessRights()->isEmpty();
    }

    public function hasAccessRight($ar)
    {
        return in_array($ar, $this->getAccessRights());
    }
}
