<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-03-09 11:23:56
 */

namespace Azimut\Bundle\SecurityBundle\Security;

use Azimut\Component\PHPExtra\TraitHelper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class BaseAccessRoleService
{
    protected $roles = array();
    protected $rolesOnEntities = array();
    protected $entities = array();
    protected $namespace;
    protected $registry;
    protected $name;


    public function __construct(RegistryInterface $registry, $activeBackofficeApps, $name, $namespace, $appName, array $roles = [], array $rolesOnEntities = [], array $entities = [])
    {
        $this->registry = $registry;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->entities = $entities;
        if (in_array($appName, $activeBackofficeApps)) {
            $this->roles = $roles;
            if ($rolesOnEntities != null) {
                $this->rolesOnEntities = $rolesOnEntities;
            }
        }
    }

    public function supportsClass($class)
    {
        if (stripos($class, 'Proxies\\__CG__\\') === 0) {
            $class = substr($class, 15);
        }

        // accepted subclasses based on discriminator map
        foreach ($this->entities as $subclass) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->registry->getManager()->getClassMetadata($subclass);
            if (count($classMetadata->discriminatorMap) > 0) {
                if (in_array($class, $classMetadata->discriminatorMap)) {
                    return true;
                }
            }
        }
        return in_array($class, $this->entities);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getRolesOnEntities()
    {
        return $this->rolesOnEntities;
    }

    public function getMergedRolesOnEntities()
    {
        $mergedRoles = array();
        if (count($this->entities) > 0) {
            foreach ($this->rolesOnEntities as $roles) {
                $mergedRoles = array_merge($roles, $mergedRoles);
            }
        }
        return array_unique($mergedRoles);
    }

    public function getEntities()
    {
        return $this->entities;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isClassHidden($className)
    {
        return false;
    }

    public function getObjectParents($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Argument passed to '.__CLASS__.'::getObjectParents must be an object, '.gettype($object).' given');
        }

        $isObjectClassKnown = false;

        $objectClass = get_class($object);

        // if object is a Doctrine Proxy
        if ($object instanceof \Doctrine\Common\Persistence\Proxy) {
            /** @var ClassMetadata $metadata */
            $metadata = $this->registry->getManager()->getClassMetadata(get_class($object));
            $objectClass = $metadata->rootEntityName;
        }

        foreach ($this->entities as $knownClass) {
            // accepted subclasses based on discriminator map
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->registry->getManager()->getClassMetadata($knownClass);
            if (count($classMetadata->discriminatorMap) > 0) {
                if (0 === strpos($objectClass, $knownClass)) {
                    $isObjectClassKnown = true;
                    break;
                }
            }

            if ($objectClass == $knownClass) {
                $isObjectClassKnown = true;
                break;
            }
        }

        if (!$isObjectClassKnown) {
            throw new \InvalidArgumentException('Argument passed to '.__CLASS__.'::getObjectParents must be an object of a known class ('.implode(', ', $this->entities).'), '.get_class($object).' given');
        }

        if (is_object($object) && TraitHelper::isClassUsing($object, ObjectAccessRightAware::class)) {
            /** @var ObjectAccessRightAware $object */
            $parents = $object->getParentsSecurityContextObject();
            if (is_null($parents)) {
                $parents = [];
            } elseif (!is_array($parents)) {
                $parents = [$parents];
            }
            return $parents;
        }

        return [];
    }
}
