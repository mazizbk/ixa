<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-09-23 10:19:56
 */

namespace Azimut\Bundle\SecurityBundle\Security;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\Repository\AccessRightRepository;
use Azimut\Bundle\SecurityBundle\Entity\Repository\UserRepository;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\UnitOfWork;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;
use Azimut\Bundle\SecurityBundle\AccessRoles\RoleProviders;

class Voter extends BaseVoter
{
    /**
     * @var AccessRightRepository
     */
    protected $accessRightRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var BaseAccessRoleService
     */
    private $roleService;

    /**
     * @var RoleProviders
     */
    private $roleProviders;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $voteId;

    public function __construct(ContainerInterface $container, ManagerRegistry $registry, BaseAccessRoleService $roleService, RoleProviders $roleProviders, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->roleService = $roleService;
        $this->accessRightRepository  = $registry->getManager()->getRepository(AccessRight::class);
        $this->userRepository = $registry->getManager()->getRepository(User::class);
        $this->roleProviders = $roleProviders;
        $this->unitOfWork = $registry->getManager()->getUnitOfWork();
        $this->logger = $logger;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->logger) {
            $this->voteId = substr(md5(uniqid()), 0, 8); // Yeah, odd, by sufficient
            $this->logger->debug(sprintf(
                '[%s][%s] Begin vote for attribute%s %s on %s%s',
                $this->roleService->getName(),
                $this->voteId,
                count($attributes)>1?'s':'',
                implode(', ', $attributes),
                gettype($object),
                is_object($object)?(get_class($object).(method_exists($object, 'getId')?'#'.$object->getId():'')):''
            ));
        }

        if (is_string($object) && !$this->supportsClass($object)) {
            if ($this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] $object is an unsupported string ('.$object.'), abstaining from voting');
            }
            return self::ACCESS_ABSTAIN;
        } elseif (is_object($object) && !$this->supportsClass(get_class($object))) {
            if ($this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] $object is of an unsupported class ('.get_class($object).'), abstaining from voting');
            }
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                if ($this->logger) {
                    $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] Unsupported attribute '.$attribute);
                }
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($attribute, $object, $token)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
            if ($this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] Not allowed by any mean');
            }
        }

        return $vote;
    }

    /**
     * Checks whether or not the current user has the specific rights for the object
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!is_object($user)) {
            return false;
        }

        //user returned from token of instance HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser
        //find user from db
        if (!$user instanceof User && !($user =  $this->userRepository->findOneBy(['username' => $user->getUsername()]))) {
            // TODO Exception?
            return false;
        }

        // Get users global rights
        // Check super_admin (with global)
        if ($this->isSuperAdmin($user)) {
            if ($this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] User is super admin, allowing');
            }
            return true;
        }

        if (is_null($subject)) {
            $hasAppRole = false;
            $hasGlobalRole = false;

            if ('APP' == explode("_", $attribute)[0]) {
                $hasAppRole = $this->hasAppRole($user, $attribute);
            }
            else {
                $hasGlobalRole = $this->hasGlobalRole($user, $attribute);
            }

            // Log debug
            if ($hasAppRole && $this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] User has required app role');
            }
            if (!$hasAppRole && $this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] User does not have required app role');
            }
            if ($hasGlobalRole && $this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] User has required global role');
            }
            if (!$hasGlobalRole && $this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] User does not have required global role');
            }

            return $hasAppRole || $hasGlobalRole;
        }

        if ($this->isGrantedByParentsObject($subject, $user, $attribute)) {
            if ($this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] Is granted by one of the object\'s parents');
            }
            return true;
        }
        if ($attribute == 'VIEW') {
            $isGrantedByChildren = $this->isGrantedByChildren($subject, $user, $attribute);

            if ($isGrantedByChildren && $this->logger) {
                $this->logger->debug('['.$this->roleService->getName().']['.$this->voteId.'] Is granted by one of the object\'s children (VIEW attribute only)');
            }

            return $isGrantedByChildren;
        }

        return false;
    }

    protected function hasAppRole(User $user, $role)
    {
        return count($this->accessRightRepository->getUserAppAccessRights($user, str_replace('*', '%', $role))) > 0;
    }

    protected function hasGlobalRole(User $user, $role)
    {
        return count($this->accessRightRepository->getUserGlobalAccessRights($user, str_replace('*', '%', $role))) > 0;
    }

    protected function isSuperAdmin(User $user)
    {
        return $result = count($this->accessRightRepository->getUserGlobalAccessRights($user, 'SUPER_ADMIN')) > 0;
    }

    private function isGrantedByParentsObject($object, User $user, $attribute)
    {
        /** @var ObjectAccessRightAware $object */
        if (is_object($object)) {
            if (!$this->isNewEntity($object)) {
                if (count($this->accessRightRepository->getUserAccessRightsOn($user, $object, $attribute)) > 0) {
                    return true;
                }
            }
            $className = get_class($object);
        } else {
            $className = $object;
        }
        if (count($this->accessRightRepository->getUserAccessRightsClass($user, $className, $attribute)) > 0) {
            return true;
        }

        // Check parents
        $parents = [];
        if (is_object($object)) {
            $parents = $this->roleProviders->getProviderForClass(get_class($object))->getObjectParents($object);
        }

        $method = new \ReflectionMethod($object, 'getParentsClassesSecurityContextObject');
        $parentClasses = $method->invoke(null);
        if (is_null($parentClasses)) {
            $parentClasses = [];
        } elseif (!is_array($parentClasses)) {
            $parentClasses = [$parentClasses];
        }
        $parents = array_unique(array_merge($parents, $parentClasses));

        // Remove class from parents, preventing infinite recursion :)
        if (($key = array_search($className, $parents)) !== false) {
            unset($parents[$key]);
        }

        if (is_array($parents)) {
            foreach ($parents as $parent) {
                if ($parent && $this->isGrantedByParentsObject($parent, $user, $attribute)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function isGrantedGlobal($attribute, User $user)
    {
        return count($this->accessRightRepository->getUserGlobalAccessRights($user, $attribute)) > 0;
    }

    private function isGrantedByChildren($object, User $user, $attribute)
    {
        /** @var ObjectAccessRightAware $object */
        if (!method_exists($object, 'getChildrenSecurityContextObject') || !is_object($object)) {
            //FIXME
            //die;
            //die('Object of type '.is_object($object)?get_class($object):get_type($object).' doesn\'t have the getChildrenSecurityContextObject method');
        }
        if (is_array($object->getChildrenSecurityContextObject()) || $object->getChildrenSecurityContextObject() instanceof Collection) {
            /** @var ObjectAccessRightAware $child */
            foreach ($object->getChildrenSecurityContextObject() as $child) {
                if (count($this->accessRightRepository->getUserAccessRightsOn($user, $child)) > 0) {
                    return true;
                }
                $className = get_class($child);
                if (count($this->accessRightRepository->getUserAccessRightsClass($user, $className, $attribute)) > 0) {
                    return true;
                }
                if ($this->isGrantedByChildren($child, $user, $attribute)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isNewEntity($entity)
    {
        return !$this->unitOfWork->isInIdentityMap($entity) || $this->unitOfWork->isScheduledForInsert($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // Not used in this context
    }

    public function supportsClass($class)
    {
        foreach ($this->roleService->getEntities() as $supportedClass) {
            if ($supportedClass === $class || is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    public function supportsAttribute($attribute)
    {
        $supportedAttributes = array_merge($this->roleService->getMergedRolesOnEntities(), $this->roleProviders->getRoles());

        // if attribute ends with wilcard
        if ('*' == substr($attribute, -1)) {
            $wildcardPrefix = substr($attribute, 0, -1);
            foreach ($supportedAttributes as $supportedAttribute) {
                if (0 === strpos($supportedAttribute, $wildcardPrefix)) {
                    return true;
                }
            }
        }

        return in_array($attribute, $supportedAttributes);
    }
}
