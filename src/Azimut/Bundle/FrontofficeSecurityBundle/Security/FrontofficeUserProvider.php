<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-30 12:17:08
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Security;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

class FrontofficeUserProvider implements UserProviderInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->registry->getManager()->getRepository(FrontofficeUser::class)
            ->findActiveOneByEmail($username)
        ;

        if (!$user) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof FrontofficeUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return FrontofficeUser::class === $class || Consultant::class === $class;
    }
}
