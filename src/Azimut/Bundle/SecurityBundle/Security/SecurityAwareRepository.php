<?php
/**
 * Created by mikaelp on 8/22/2016 3:22 PM
 */

namespace Azimut\Bundle\SecurityBundle\Security;

interface SecurityAwareRepository
{
    /**
     * @param string|null $expectedClass
     * @return ObjectAccessRightAware[]
     */
    public function findSecurityObjects($expectedClass = null);
}
