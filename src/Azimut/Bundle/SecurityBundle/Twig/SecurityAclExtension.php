<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-15 16:18:27
 */

namespace Azimut\Bundle\SecurityBundle\Twig;

use Azimut\Bundle\SecurityBundle\Entity\AclField;

class SecurityAclExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('acl_field', array($this, 'aclFilter')), // Twig_SimpleFunction
        );
    }

    public function aclFilter($obj, $field)
    {
        $acl_field = new AclField($obj, $field);

        return $acl_field;
    }

    public function getName()
    {
        return 'security_acl_extension';
    }
}
