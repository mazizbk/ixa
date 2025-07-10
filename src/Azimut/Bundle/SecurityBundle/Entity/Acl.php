<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-08 10:30:41
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="Azimut\Bundle\SecurityBundle\Entity\Repository\AclRepository")
* @ORM\Table(name="security_acl")
*/
class Acl
{
    const NO_FIELD_VIEW = 'NO_FIELD_VIEW';
    const NO_FIELD_EDIT = 'NO_FIELD_EDIT';

    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
    * @ORM\Column(type="string", length=64)
    */
    protected $objectClass;

    /**
    * @ORM\Column(type="string", length=64, nullable=true)
    */
    protected $objectId;

    /**
    * @ORM\Column(type="json_array")
    */
    protected $fields = array();

    /**
    * @ORM\ManyToOne(targetEntity="Azimut\Bundle\SecurityBundle\Entity\AccessRightAcl", inversedBy="acl")
    * @ORM\JoinColumn(name="access_rightc_acl_id", referencedColumnName="id")
    */
    protected $accessRight;

    public function __construct($objectClass = null, $objectId = null)
    {
        $this->objectClass = $objectClass;
        $this->objectId = $objectId;
    }

    public function getObjectClass()
    {
        return $this->objectClass;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function getAccessRight()
    {
        return $this->accessRight;
    }

    public function setAccessRight($accessRight)
    {
        return $this->accessRight = $accessRight ;
    }

    /**
    * @return Acl
    */
    public function setNotEditable($field, $editable = true)
    {
        return $this->setPermission($field, self::NO_FIELD_EDIT, !$editable);
    }

    /**
    * @return Acl
    */
    public function setNotViewable($field, $viewable = true)
    {
        return $this->setPermission($field, self::NO_FIELD_VIEW, !$viewable);
    }

    public function isEditable($field)
    {
        if (!isset($this->fields[$field])) {
            return true;
        }

        return (!in_array(self::NO_FIELD_VIEW, $this->fields[$field]) && !(in_array(self::NO_FIELD_EDIT, $this->fields[$field])));
    }

    public function isViewable($field)
    {
        if (!isset($this->fields[$field])) {
            return true;
        }

        return !in_array(self::NO_FIELD_VIEW, $this->fields[$field]);
    }

    private function setPermission($field, $permission, $delete = false)
    {
        //if delete==true we remove the permission from the list of permissions
        if ($delete && isset($this->fields[$field])) {
            $this->fields[$field] = array_diff($this->fields[$field], array($permission));

            return $this;
        }

        if (!$delete && (!isset($this->fields[$field]) || !in_array($permission, $this->fields[$field]))) {
            $this->fields[$field][] = $permission;
        }

        return $this;
    }
}
