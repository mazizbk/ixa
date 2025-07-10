<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2013-09-13
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

class PageContentRepository extends EntityRepository
{
    // Page status
    const STATUS_DRAFT = 'Draft';
    const STATUS_REVIEWED = 'Reviewed';
    const STATUS_PUBLISH = 'Published';
    const STATUS_HIDDEN = 'Hidden';

    /**
    * Return list of available status
    *
    * @return array
    */
    public static function getAvailableStatus()
    {
        return array(
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_REVIEWED => self::STATUS_REVIEWED,
            self::STATUS_PUBLISH => self::STATUS_PUBLISH,
            self::STATUS_HIDDEN => self::STATUS_HIDDEN
        );
    }

    public function findBySiteAndStandaloneRouterController(Site $site, $routerControllerName)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.layout', 'l')
            ->where('p.site = :site')
            ->andWhere('l.standaloneRouterController = :routerControllerName')
            ->setParameter('site', $site)
            ->setParameter('routerControllerName', $routerControllerName)
            ->getQuery()->getResult()
        ;
    }

    public function findByStandaloneRouterController($routerControllerName)
        {
            return $this->createQueryBuilder('p')
                ->leftJoin('p.layout', 'l')
                ->andWhere('l.standaloneRouterController = :routerControllerName')
                ->setParameter('routerControllerName', $routerControllerName)
                ->getQuery()->getResult()
            ;
        }
}
