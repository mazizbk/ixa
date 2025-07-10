<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-07 14:39:20
 */

namespace Azimut\Bundle\SecurityBundle\Provider;

use Azimut\Bundle\AzimutLoginBundle\Model\User as ALUser;
use Azimut\Bundle\AzimutLoginBundle\Service\AzimutLoginClient;
use Azimut\Bundle\SecurityBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOAuthUserProvider;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider extends BaseOAuthUserProvider
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var AzimutLoginClient
     */
    private $azimutLoginClient;

    public function __construct(ManagerRegistry $doctrine, AzimutLoginClient $azimutLoginClient)
    {
        $this->doctrine = $doctrine;
        $this->azimutLoginClient = $azimutLoginClient;
    }

    /**
     * Load user by its username
     * This method is called by Symfony, at every authenticated request. This method is responsible
     * for fetching the user that is already in session. If an OAuthUser is returned (which is the default
     * behavior), HWIOAuthBundle will reload the user from the OAuth server, at each request
     * @param string $username
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $userRepo = $this->doctrine->getManager()->getRepository(User::class);
        /** @var User $user */
        $user = $userRepo->findOneBy(['username' => $username]);
        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException();
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        // Following REST principles, we answer with an object, containing a "user" property
        $serverResponse = $response->getData();
        if (array_key_exists('user', $serverResponse)) {
            $response->setData($serverResponse['user']);
        }

        $userRepo = $this->doctrine->getManager()->getRepository(User::class);
        /** @var User $user */
        $user = $userRepo->findOneBy(['username' => $response->getNickname()]);

        if ($user) {
            if ($response instanceof PathUserResponse) {
                foreach ($response->getPaths() as $server => $local) {
                    $setter = 'set'.ucfirst($local);
                    if (method_exists($user, $setter) && array_key_exists($server, $response->getData())) {
                        $user->{$setter}($response->getData()[$server]);
                    }
                }
                $this->doctrine->getManager()->persist($user);
                $this->doctrine->getManager()->flush();
            }

            return $user;
        }
        if (array_key_exists('id', $response->getData())) {
            $azLogUser = ALUser::fromAPIResponse($response->getData());
            $this->azimutLoginClient->unauthorizeUser($azLogUser);
        }

        throw new UsernameNotFoundException();
    }

    public function supportsClass($class)
    {
        return $class === User::class || parent::supportsClass($class);
    }
}
