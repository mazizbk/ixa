<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-11 11:57:29
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

class FrontofficeUserRepository extends EntityRepository
{
    public function findActiveOneByEmail($email)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.isActive = 1')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findOneByValidResetToken($token)
    {
        $limitDateTime = new \DateTime('-'. FrontofficeUser::RESET_TOKEN_LIFETIME .' minutes');

        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.resetToken = :token')
            ->andWhere('u.resetTokenDateTime > :limitDateTime')
            ->setParameter('token', $token)
            ->setParameter('limitDateTime', $limitDateTime)
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb->getQuery()->getOneOrNullResult();
    }
}
