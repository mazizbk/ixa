<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-12 12:13:56
 */

namespace Azimut\Bundle\SecurityBundle\Acl;

use Doctrine\Bundle\DoctrineBundle\Registry;

class AclService
{
    private $resolver;
    private $repository;

    public function __construct(AclResolverInterface $resolver, Registry $doctrine)
    {
        $this->resolver = $resolver;
        $this->repository = $doctrine->getRepository('AzimutSecurityBundle:Acl');
    }

    //class can be an object or a class
    public function getEditableClassAcl($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class) || !class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        //returns whats after last \
        $name = $this->resolver->getObjectClass($class);

        return $this->repository->findOneByObjectClass($name);
    }

    public function getEditableObjectAcl($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Expected an object, got a "%s".', gettype($object)));
        }

        $name = $this->resolver->getObjectClass($object);
        $id = $this->resolver->getObjectId($object);

        return $this->repository->findOneByObject($name, $id);
    }

    public function getAclList($object)
    {
        $objectClass = $this->resolver->getObjectClass($object);
        $id = $this->resolver->getObjectId($object);

        return $this->repository->getAclList($objectClass, $id);
    }
}
