<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-11-23 14:43:00
 */

namespace Azimut\Bundle\SecurityBundle\Services;

use Azimut\Bundle\SecurityBundle\Entity\User;
use Doctrine\Common\Persistence\Proxy;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use \Doctrine\Common\Util\ClassUtils;

class SecurityFilter
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    public function __construct(Serializer $serializer, TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker, UserManager $userManager, AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userManager = $userManager;
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * @return null|User
     */
    protected function getCurrentUser()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user && !$user instanceof User) {
            /** @var OAuthUser $user */
            $user = $this->userManager->findUserByUsername($user->getUsername());
        }

        return $user;
    }

    public function serializeGroup($data, $groups = [])
    {
        $serializationContext = new SerializationContext();
        $serializationContext->setGroups($groups);

        return $this->serialize($data, $serializationContext);
    }

    public function serialize($data, SerializationContext $serializationContext = null)
    {
        if (is_null($serializationContext)) {
            $serializationContext = new SerializationContext();
        }

        if (is_array($data) || $data instanceof \Traversable) {
            $finalData = [];
            foreach ($data as $key => $value) {
                $value = $this->serialize($value, $serializationContext);
                if ($value) {
                    $finalData[] = $value;
                }
            }
            return $finalData;
        } elseif (is_object($data)) {
            // Do not handle native objects
            if (false === strpos(get_class($data), '\\')) {
                return $data;
            }
            if ($data instanceof Proxy) {
                $data->__load();
            }
            return $this->serializeObject($data, $serializationContext);
        }

        return $data;
    }

    protected function serializeObject($data, SerializationContext $serializationContext)
    {
        if (!$this->authorizationChecker->isGranted('VIEW', $data)) {
            return null;
        }
        $finalArray = [];
        $className = ClassUtils::getClass($data);

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->serializer->getMetadataFactory()->getMetadataForClass($className);
        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            if (!$serializationContext->getExclusionStrategy()->shouldSkipProperty($propertyMetadata, $serializationContext)) {
                $finalArray[$propertyMetadata->name] = $this->serialize($propertyMetadata->getValue($data), $serializationContext);
            }
        }

        return $finalArray;
    }
}
