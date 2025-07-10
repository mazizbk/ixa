<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-12 12:07:32
 */

namespace Azimut\Bundle\SecurityBundle\Acl;

interface AclResolverInterface
{
    public function getObjectClass($class);
    public function getObjectId($object);
}
