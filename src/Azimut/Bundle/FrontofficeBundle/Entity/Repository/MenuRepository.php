<?php

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;

class MenuRepository extends EntityRepository
{
    public function findOneByHostAndPlaceholder($host, $placeholder)
    {
        return $this
            ->createQueryBuilder('m')
            ->leftJoin('m.site', 's')
            ->leftJoin('s.mainDomainName', 'dn')
            ->leftJoin('m.menuDefinition', 'md')
            ->where('dn.name = :host AND md.placeholder = :placeholder')
            ->setParameter('host', $host)
            ->setParameter('placeholder', $placeholder)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySiteAndName(Site $site, $name)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.menuDefinition', 'md')
            ->addSelect('md')
            ->where('m.site = :site')
            ->andWhere('md.placeholder = :name')
            ->setParameter('site', $site)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
