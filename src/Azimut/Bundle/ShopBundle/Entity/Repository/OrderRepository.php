<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 10:38:47
 */

namespace Azimut\Bundle\ShopBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

class OrderRepository extends EntityRepository
{
    public function findAllHavingNumber()
    {
        return $this->createQueryBuilder('o')
            ->where('o.number is not null')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTodayLastOrderHavingNumber()
    {
        return $this->createQueryBuilder('o')
            ->where('o.number is not null')
            ->andWhere('o.orderDate = CURRENT_DATE()')
            ->orderBy('o.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findUserPlacedOrders(FrontofficeUser $user)
    {
        return $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->andWhere('o.number is not null')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
