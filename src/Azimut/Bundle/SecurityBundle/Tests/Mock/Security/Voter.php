<?php
/**
 * Created by mikaelp on 8/30/2016 11:48 AM
 */

namespace Azimut\Bundle\SecurityBundle\Tests\Mock\Security;

use Azimut\Bundle\SecurityBundle\Security\Voter as BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Mocks base Voter so that access is always allowed if class and attribute are supported
 */
class Voter extends BaseVoter
{
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (is_string($object) && !$this->supportsClass($object)) {
            return self::ACCESS_ABSTAIN;
        } elseif (is_object($object) && !$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            return self::ACCESS_GRANTED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
