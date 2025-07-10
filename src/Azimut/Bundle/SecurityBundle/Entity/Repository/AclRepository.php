<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2013-11-12 12:29:50
 */

namespace Azimut\Bundle\SecurityBundle\Entity\Repository;

use Azimut\Bundle\SecurityBundle\Entity\Acl;
use Doctrine\ORM\EntityRepository;

class AclRepository extends EntityRepository
{
    public function getAclList($objectClass, $id)
    {
        return $this->getEntityManager()->createQuery('
			SELECT a
			FROM AzimutSecurityBundle:Acl a
			WHERE
			(a.objectClass = :objectClass AND a.objectId IS NULL)
			OR
			(a.objectClass = :objectClass AND a.objectId = :id)
			')
            ->setParameters(array('objectClass' => $objectClass, 'id' => $id))
            ->execute()
        ;
    }

    public function findOneByObjectClass($objectClass)
    {
        return $this->findOneByObject($objectClass, null);
    }

    public function findOneByObject($objectClass, $id)
    {
        $result = $this->findOne(array(
            'objectClass' => $objectClass,
            'objectId' => $id
            ));

        if (!$result) {
            $result = new Acl($objectClass, $id);
        }

        return $result;
    }
}
