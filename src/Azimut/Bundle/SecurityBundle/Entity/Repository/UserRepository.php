<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-07 14:49:21
 */

namespace Azimut\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    private const SUPER_ADMIN = 'SUPER_ADMIN';
    private const ADMIN_QUERY = 'SELECT u2 FROM AzimutSecurityBundle:User u2 LEFT JOIN u2.accessRights ar LEFT JOIN ar.roles r WHERE r.role = :role';

    public function getUserList()
    {
        return
            $this->getEntityManager()->createQuery('
                SELECT u
                FROM AzimutSecurityBundle:User u
                WHERE u NOT IN ('.self::ADMIN_QUERY.')
            ')
            ->setParameters(['role' => self::SUPER_ADMIN])
            ->execute()
        ;
    }

    public function findSuperAdminUsers()
    {
        return
            $this->getEntityManager()->createQuery('
                SELECT u
                FROM AzimutSecurityBundle:User u
                WHERE u IN ('.self::ADMIN_QUERY.')
            ')
            ->setParameters(['role' => self::SUPER_ADMIN])
            ->execute()
        ;
    }

    public function getUsersCount()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(u.id) FROM AzimutSecurityBundle:User u WHERE u NOT IN ('.self::ADMIN_QUERY.')')
            ->setParameters(['role' => self::SUPER_ADMIN])
            ->getSingleScalarResult()
        ;
    }
}
