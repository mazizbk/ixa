<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-10-07 14:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Security;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/** @DI\Service */
class RoleEvaluator
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @DI\InjectParams({
     *     "authorizationChecker" = @DI\Inject("security.authorization_checker"),
     * })
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /** @DI\SecurityFunction("isAuthorized")
     *
     * Checks if user has global role or app_role
     */
    public function isAuthorized($role)
    {
        // Role SUPER_ADMIN is automagically checked
        return $this->authorizationChecker->isGranted($role);
    }
}
