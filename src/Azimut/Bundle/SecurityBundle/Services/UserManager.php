<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-07 09:16:56
 */

namespace Azimut\Bundle\SecurityBundle\Services;

use Azimut\Bundle\SecurityBundle\Entity\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Entity\User;

/**
 *  User Manager implementation.
 */
class UserManager implements UserProviderInterface
{
    /**
     * @var Canonicalizer
     */
    protected $canonicalizer;

    protected $objectManager;

    protected $class;

    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param Canonicalizer $canonicalizer
     * @param ObjectManager $om
     * @param string        $class
     */
    public function __construct(Canonicalizer $canonicalizer, ObjectManager $om, $class)
    {
        $this->canonicalizer = $canonicalizer;

        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Returns an empty user instance
     *
     * @return SecurityUserInterface
     */
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class();

        return $user;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Finds a user by email
     *
     * @param string $email
     *
     * @return SecurityUserInterface
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $this->canonicalize($email)));
    }

    /**
     * Finds a user by username
     *
     * @param string $username
     *
     * @return SecurityUserInterface
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array('usernameCanonical' => $this->canonicalize($username)));
    }


    /**
     * Finds a user by oauthId
     *
     * @param string $oauthId
     *
     * @return SecurityUserInterface
     */
    public function findUserByOauthId($oauthId)
    {
        return $this->findUserBy(array('oauthId' => $oauthId));
    }
    /**
     * Finds a user either by email, or username
     *
     * @param string $usernameOrEmail
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @return User
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (!filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            throw new UsernameNotFoundException('Account is not supported.');
        }

        return $this->findUserBy(['username' => $usernameOrEmail]);
    }

    /**
     * Refreshed a user by User Instance
     *
     * @param SecurityUserInterface $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @return SecurityUserInterface
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException('Account is not supported.');
        }
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of Azimut\Bundle\SecurityBundle\Entity\User, but got "%s".', get_class($user)));
        }

        $refreshedUser = $this->findUserBy(array('id' => $user->getId()));
        if (null === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * Loads a user by username
     * @param string $username
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @return SecurityUserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function updateCanonicalFields(User $user)
    {
        $username = $user->getUsername();

        $canonicalUsername = $this->canonicalize($username);

        $user->setUsernameCanonical($canonicalUsername);
        $user->setEmailCanonical($canonicalUsername);
        $user->setEmail($username);
    }

    public function updatePassword(User $user)
    {
        if (0 === mb_strlen($user->getPassword())) {
            $user->setPassword('0000');
        }
    }

    /**
     * Canonicalizes an email or username
     *
     * @param string emailOrUsername
     *
     * @return string
     */
    protected function canonicalize($emailOrUsername)
    {
        return $this->canonicalizer->canonicalize($emailOrUsername);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(User $user)
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findUsers()
    {

      //  return $this->repository->findAll();  ///to ad method find users except super_admin
        return $this->repository->getUserList();
    }

    public function findSuperAdminUsers()
    {
        return $this->repository->findSuperAdminUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function reloadUser(User $user)
    {
        $this->objectManager->refresh($user);
    }

    public function flushUser()
    {
        $this->objectManager->flush();
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean                                              $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(User $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
}
