<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 15:22:25
 */

namespace Azimut\Bundle\ModerationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Azimut\Bundle\FrontofficeBundle\Entity\Zone;

class CmsFileBufferRepository extends EntityRepository
{
    public function getAvailableTypes()
    {
        return array_keys($this->getClassMetadata()->discriminatorMap);
    }

    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No CmsFileBuffer of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function countWaiting() {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(c.id) FROM AzimutModerationBundle:CmsFileBuffer c WHERE c.isArchived = false')
            ->getSingleScalarResult()
        ;
    }

    public function countWaitingInTargetZone(Zone $zone) {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(c.id) FROM AzimutModerationBundle:CmsFileBuffer c WHERE c.targetZone = :zone AND c.isArchived = false')
            ->setParameter('zone', $zone)
            ->getSingleScalarResult()
        ;
    }
}
