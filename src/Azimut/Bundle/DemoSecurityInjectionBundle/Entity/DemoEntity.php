<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 10:37:39
*/

namespace Azimut\Bundle\DemoSecurityInjectionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware; //trait for access rights

/**
 * @ORM\Table(name="demo_security_entity")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\DemoSecurityInjectionBundle\Entity\Repository\EntityRepository")
 */
class DemoEntity
{
    use ObjectAccessRightAware;
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_entities","detail_entity"})
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_entities","detail_entity"})
     */
    private $title;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail_entity"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AccessRightDemoEntity", mappedBy="entity")
     */
    protected $accessRights;

    public function __construct($number)
    {
        $this->title = 'Entity'.$number;
        $this->accessRights = new ArrayCollection();
        $this->name = 'Demo';
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

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

   /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return $this->getName();
    }
    /*
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'demo_entity';
    }

    public static function getAccessRightClassName()
    {
        return 'Azimut\Bundle\DemoSecurityInjectionBundle\Entity\AccessRightDemoEntity';
    }

    /*
     * Used for DemoSecurityInjectionVoter to determine the access rights.
     */
    public function getParentsSecurityContextObject()
    {
        return null;
    }

    /*
     * Used for SecurityVoter to determine the access rights class.
     */
    public static function getParentsClassesSecurityContextObject()
    {
        return null;
        //if there is a parent class 'Azimut\Bundle\DemoSecurityInjectionBundle\Entity\ParentEntity';
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        return null; //used for access right object
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [];
    }
}
