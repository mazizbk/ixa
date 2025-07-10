<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-07 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Azimut\Bundle\AzimutLoginBundle\Model\User as ALUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\SecurityBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="security_user")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="this.user.already.exists"
 * )
 */
class User implements UserInterface, \Serializable
{
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_user", "detail_user", "detail_access_right", "list_access_rights"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, unique=true)
     * @Groups({"list_user", "detail_user", "detail_access_right", "list_access_rights"})
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, unique=true)
     */
    protected $usernameCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="oauthId", type="string", length=255, nullable=true)
     */
    protected $oauthId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list_user", "detail_user"})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list_user", "detail_user"})
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, unique=true)
     * @Groups({"list_user", "detail_user"})
     */
    private $email;

    /**
     * @var string
     *
     * @Groups({"detail_user"})
     */
    protected $emailCanonical;

    /**
     * @var Group[]|ArrayCollection<Group>
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="security_user_group")
     * @Groups({"list_user", "detail_user"})
     */
    protected $groups;

    /**
     * @var AccessRight[]|ArrayCollection<AccessRight>
     *
     * @ORM\OneToMany(targetEntity="AccessRight", orphanRemoval=true, cascade={"persist", "remove"}, mappedBy="user")
     * @Groups({"detail_user"})
     */
    protected $accessRights;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $salt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40)
     */
    protected $password;

    /**
     * @var string[]
     */
    protected $roles;

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->groups = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->email = $username;
        $this->usernameCanonical = $username;
        $this->username = $this->email;
        $this->emailCanonical = $this->email;
        return $this;
    }

    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->usernameCanonical = $email;
        $this->username = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function setOauthId($id)
    {
        $this->oauthId = $id;

        return $this;
    }

    public function getOauthId()
    {
        return $this->oauthId;
    }

    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
            $group->addUser($this);
        }

        return $this;
    }

    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
            $group->removeUser($this);
        }

        return $this;
    }

    /**
     * @return AccessRight[]|ArrayCollection<AccessRight>
     */
    public function getAccessRights()
    {
        return $this->accessRights;
    }

    public function addAccessRight(AccessRight $accessRight)
    {
        if (!$this->accessRights->contains($accessRight)) {
            $this->accessRights->add($accessRight);
            $accessRight->setUser($this);
        }

        return $this;
    }

    public function removeAccessRight(AccessRight $accessRight)
    {   //if right belongs just to user and not to his group
        if ($this->accessRights->contains($accessRight)) {
            $this->accessRights->removeElement($accessRight);
        }

        return $this;
    }

    //if the user has no direct access right nor have his groups return false
    public function hasAccessRights($inherits = false)
    {
        if (!$inherits) {
            return !$this->getAccessRights()->isEmpty();
        } elseif ($this->getAccessRights()->isEmpty()) {
            $hasAr = false;
            foreach ($this->getGroups() as $group) {
                if ($group->hasAccessRights()) {
                    $hasAr = true;
                }
            }
            return $hasAr;
        } else {
            return true;
        }
    }


    public function hasAccessRight($ar, $inherits=false)
    {
        if (!$inherits) {
            return $this->getAccessRights()->contains($ar);
        } else {
            $found = false;
            foreach ($this->getGroups() as $group) {
                if ($group->getAccessRights()->contains($ar)) {
                    $found = true;
                }
            }
            return ($this->getAccessRights()->contains($ar) || $found);
        }
    }

    /**
     * Serializes the user.
     * @see \Serializable::serialize()
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt
        ));
    }

    /**
     * Unserializes the user.
     *
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        return $this->salt = $salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Returns true if the user has a global role or app_role with the given role
     * or it belongs to a group which has a global role or app_role with the given role
     */
    public function hasRole($role)
    {
        trigger_error('User::hasRole has been deprecated. Use SecurityChecker::isGranted instead', E_USER_ERROR);
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return ALUser
     */
    public function toLoginUser()
    {
        return ALUser::fromAPIResponse([
            'id' => $this->getOauthId(),
            'email' => $this->getEmail(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
        ]);
    }

    public function isConfirmed()
    {
        return !is_null($this->firstName) && !is_null($this->lastName);
    }
}
