<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-12 12:03:17
 */

namespace Azimut\Bundle\SecurityBundle\Acl;

use Symfony\Bridge\Doctrine\RegistryInterface;

class AclResolver implements AclResolverInterface
{
    protected $doctrine;

    public function __construct(RegistryInterface $registry)
    {
        $this->doctrine = $registry;
    }

    /**
    * {@inheritdoc}
    */
    public function getObjectClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (false !== $pos = strrpos($class, '\\')) {
            $class = substr($class, $pos + 1);
        }

        return strtolower($class);
    }

    /**
    * {@inheritdoc}
    */
    public function getObjectId($object)
    {
        $class = get_class($object);
        if (!is_object($object)) {
            return null;
        }
        $manager = $this->doctrine->getManagerForClass($class);

        $id = $manager->getUnitOfWork()->getEntityIdentifier($object);
        if (is_array($id)) {
            $id = implode(',', $id);
        }

        return $id;
    }
}
