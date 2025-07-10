<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-07 10:07:39
 */

namespace Azimut\Bundle\SecurityBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Entity\Group;

class GroupManager
{
    protected $objectManager;
    protected $class;
    protected $repository;

    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function createGroup()
    {
        $class = $this->getClass();

        return new $class();
    }

    public function findGroupByName($name)
    {
        return $this->findGroupBy(array('name' => $name));
    }

    public function deleteGroup(Group $group)
    {
        $this->objectManager->remove($group);
        $this->objectManager->flush();
    }

     /**
     * Finds one group by the given criteria.
     *
     * @param array $criteria
     *
     * @return Group
     */
    public function findGroupBy(array $criteria)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->findOneBy($criteria);
    }

     /**
     * Returns a collection with all user instances.
     *
     * @return Group[]
     */
    public function findGroups()
    {
        return $this->repository->findAll();
    }

    /**
     * Returns the group's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    public function updateGroup(Group $group, $andFlush = true)
    {
        $this->objectManager->persist($group);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function getRepository()
    {
        return $this->repository;
    }
}
