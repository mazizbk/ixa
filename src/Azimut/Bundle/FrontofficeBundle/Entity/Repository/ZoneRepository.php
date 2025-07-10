<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-17 16:39:32
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;

class ZoneRepository extends EntityRepository
{
    public function findOneByNameAndPage($page, $name)
    {
        return $this->createQueryBuilder('z')
            ->leftJoin('z.zoneDefinition', 'zd')
            ->addSelect('zd')
            ->where('z.pageContent = :page')
            ->andWhere('zd.name = :name')
            ->setParameter('page', $page)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Shift down attachments display order in a zone starting at a given display order value
     *
     * @param Zone $zone
     * @param int $startDisplayOrder
     *
     * @return mixed
     */
    public function decreaseAttachmentsDisplayOrderStartingAt(Zone $zone, $startDisplayOrder)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update(ZoneCmsFileAttachment::class, 'za')
            ->set('za.displayOrder', 'za.displayOrder - 1')
            ->where('za.zone = :zone')
            ->andWhere('za.displayOrder >= :displayOrder')
            ->setParameter('zone', $zone)
            ->setParameter('displayOrder', $startDisplayOrder)
            ->getQuery()
            ->execute()
        ;
    }
}
