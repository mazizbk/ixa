<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-26 09:29:50
 */

namespace Azimut\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AccessRightClassRepository extends EntityRepository
{
    public function getListClass($class)
    {
        return $this->getEntityManager()->createQuery('
            SELECT a
            FROM AzimutSecurityBundle:AccessRightClass a
            WHERE
            (a.class = :class)
            ')
            ->setParameters(array('class' => $class))
            ->execute()
        ;
    }
}
