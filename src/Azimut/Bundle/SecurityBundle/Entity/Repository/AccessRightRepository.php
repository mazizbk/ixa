<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-07 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Entity\Repository;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAppRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Component\PHPExtra\TraitHelper;
use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class AccessRightRepository extends EntityRepository
{
    public function getUserGlobalAccessRights(User $user, $attribute)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('r')
            ->from(AccessRightRoles::class, 'r')
        ;

        $query = self::filterAccessRights($user, $attribute, $qb)->getQuery();

        $query->setFetchMode(AccessRight::class, "roles", ClassMetadata::FETCH_EAGER);

        return $query->getResult();
    }

    public function getUserAppAccessRights(User $user, $attribute)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('r')
            ->from(AccessRightAppRoles::class, 'r')
        ;

        $query = self::filterAccessRights($user, $attribute, $qb)->getQuery();

        $query->setFetchMode(AccessRight::class, "roles", ClassMetadata::FETCH_EAGER);

        return $query->getResult();
    }

    /**
     * @param User $user
     * @param string $class
     * @todo Check if class exists
     * @return mixed
     */
    public function getUserAccessRightsClass(User $user, $class, $attribute)
    {
        $classes = array_merge(array($class), array_values(class_parents($class)));
        $classes = array_map(function ($value) {
            if (stripos($value, 'Proxies\\__CG__\\') === 0) {
                $value = substr($value, 15);
            }
            return $value;
        }, $classes);
        $qb = $this->_em->createQueryBuilder()
            ->select('r')
            ->from(AccessRightClass::class, 'r')
            ->where('r.class IN(:classes)')
            ->setParameter(':classes', $classes);

        return self::filterAccessRights($user, $attribute, $qb)->getQuery()->getResult();
    }

    /**
     * @param User                   $user
     * @param ObjectAccessRightAware $object
     * @param null                   $attribute
     * @return AccessRight[]
     */
    public function getUserAccessRightsOn(User $user, $object, $attribute = null)
    {
        if(!TraitHelper::isClassUsing($object, ObjectAccessRightAware::class)) {
            throw new \InvalidArgumentException('$object does not use trait '.ObjectAccessRightAware::class);
        }

        $accessRightClass = $object->getAccessRightClassName();
        $qb = $this->_em->createQueryBuilder()
            ->select('r')
            ->from($accessRightClass, 'r')
            ->where('r.'.$object->getAccessRightType().' = :object')
            ->setParameter(':object', $object)
        ;

        return self::filterAccessRights($user, $attribute, $qb)->getQuery()->getResult();
    }


    public function getAvailableTypes()
    {
        $types = array();

        foreach ($this->getClassMetadata()->discriminatorMap as $type => $class) {
            array_push($types, array(
                'type' => $type
            ));
        }

        return $types;
    }

    public function findAccessRightsByUser($user, $type, ParameterBag $requestParams = null)
    {
        $aright = [];
        switch ($type) {
            case "app_roles":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightAppRoles a
                WHERE
                (a.user = :user)
                ')
                    ->setParameters(array('user' => $user))
                    ->execute()
                    ;
                break;
            case "roles":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightRoles a
                WHERE
                (a.user = :user)
                ')
                    ->setParameters(array('user' => $user))
                    ->execute()
                ;
                break;
            case "class":
                $class = $requestParams->get('access_right')['accessRightType']['class'];
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightClass a
                WHERE
                (a.user = :user AND a.class = :class)
                ')
                    ->setParameters(array('user' => $user, 'class'=> $class))
                    ->execute()
                ;
                break;
            case "acl":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightAcl a')
                    ->execute()
                ;
                break;
            default:
                $class = $this->getClassFromString($type);

                /** @var $class $arights */
                $arights = $this->getEntityManager()->createQuery('
                SELECT a
                FROM '.$class.' a
                WHERE
                (a.user = :user)
                ')
                    ->setParameters(array(
                        'user' => $user
                    ))
                    ->execute()
                ;

                foreach ($arights as $arObject) {
                    if ($arObject->getObjectId() == $requestParams->get('access_right')['accessRightType']['objectId']) {
                        $aright[] = $arObject;
                    }
                }
        }

        if (count($aright) != 0) {
            return $aright[0];
        }

        return [];
    }

    public function findAccessRightsByGroup($group, $type, ParameterBag $requestParams=null)
    {
        switch ($type) {
            case "app_roles":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightAppRoles a
                WHERE
                (a.group = :group)
                ')
                    ->setParameters(array('group' => $group))
                    ->execute()
                ;
                break;
            case "roles":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightRoles a
                WHERE
                (a.group = :group)
                ')
                    ->setParameters(array('group' => $group))
                    ->execute()
                ;
                break;
            case "class":
                echo 'case class';
                $class = $requestParams->get('access_right')['accessRightType']['class'];
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightClass a
                WHERE
                (a.group = :group AND a.class = :class)
                ')
                    ->setParameters(array('group' => $group, 'class'=> $class))
                    ->execute()
                ;
                break;
            case "acl":
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRightAcl a')
                    ->execute()
                ;
                break;
            default:
                $aright = $this->getEntityManager()->createQuery('
                SELECT a
                FROM AzimutSecurityBundle:AccessRight a
                WHERE
                (a.group = :group)
                ')
                    ->setParameters(array('group' => $group))
                    ->execute()
                ;
        }

        if ($aright != null) {
            return $aright[0];
        }

        return [];
    }

    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No AccessRight of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function getClassFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No AccessRight of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return $class;
    }

    /**
     * @param User $user
     * @param $attribute
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected static function filterAccessRights(User $user, $attribute = null, $qb)
    {
        $qb
            ->leftJoin('r.roles', 'ro')
            ->andWhere($qb->expr()->orX('r.user = :user', 'r.group IN(:groups)'))
            ->setParameter(':user', $user)
            ->setParameter(':groups', $user->getGroups()->toArray())
        ;
        if ($attribute && $attribute != 'VIEW') {
            $qb
                ->andWhere('ro.role LIKE :role')
                ->setParameter(':role', $attribute)
            ;
        }

        return $qb;
    }
}
