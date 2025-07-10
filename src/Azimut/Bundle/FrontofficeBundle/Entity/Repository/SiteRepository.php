<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-12 17:56:29
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout;
use Doctrine\ORM\EntityRepository;

class SiteRepository extends EntityRepository
{
    /**
     * @return Site[]
     */
    public function findAllWithPages()
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.mainDomainName', 'mdn')
            ->leftJoin('s.secondaryDomainNames', 'sdn')
            ->addSelect('mdn')
            ->addSelect('sdn')
            ->join('s.menus', 'sm')
            ->join('sm.pages', 'smp')
            ->join('smp.childrenPages', 'smpcp')
            ->addSelect('sm')
            ->addSelect('smp')
            ->addSelect('smpcp')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByDomainName($domainName)
    {
        /*return $this->getEntityManager()
            ->createQuery('SELECT s from AzimutFrontofficeBundle:Site s LEFT JOIN s.domainNames dn WHERE dn.name = :domainName')
            ->setParameter('domainName', $domainName)
            ->getOneOrNullResult()
        ;*/
    }

    /**
     * @param $domainName
     * @return Site|null
     */
    public function findOneActiveByDomainName($domainName, $locale = null)
    {
        $qb =  $this->createQueryBuilder('s');
        $expr = $qb->expr();

        $domainNameMatcher = $expr->orX(
            $expr->eq('mdn.name', ':domainName'),
            $expr->eq('sdn.name', ':domainName')
        );
        $qb->setParameter(':domainName', $domainName);

        if (strpos($domainName, 'www.') === 0) {
            $domainNameMatcher->addMultiple([
                $expr->eq('mdn.name', ':domainNameWWW'),
                $expr->eq('sdn.name', ':domainNameWWW'),
            ]);
            $qb->setParameter(':domainNameWWW', substr($domainName, 4));
        }
        if(!is_null($locale)) {
            $qb->andWhere('st.locale = :locale')->setParameter(':locale', $locale);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb
            ->leftJoin('s.mainDomainName', 'mdn')
            ->addSelect('mdn')
            ->leftJoin('s.secondaryDomainNames', 'sdn')
            ->leftJoin('s.layout', 'l')
            ->addSelect('l')
            ->leftJoin('s.translations', 'st')
            ->addSelect('st')
            ->andWhere($expr->andX(
                $expr->eq('s.active', 'true'),
                $domainNameMatcher
            ))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getSitesCount()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(s.id) from AzimutFrontofficeBundle:Site s')
            ->getSingleScalarResult()
        ;
    }

    public function getSitesCountByLayout(SiteLayout $siteLayout)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(s.id) from AzimutFrontofficeBundle:Site s WHERE s.layout = :layout')
            ->setParameter('layout', $siteLayout)
            ->getSingleScalarResult()
        ;
    }
}
