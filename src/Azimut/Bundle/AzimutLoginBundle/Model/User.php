<?php
/**
 * Created by mikaelp on 12/22/2015 11:42 AM
 */

namespace Azimut\Bundle\AzimutLoginBundle\Model;

class User
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    public $isSuperAdmin = false;

    /**
     * @param array $user
     * @return User
     */
    public static function fromAPIResponse(array $user)
    {
        $userO = new self();
        /** @noinspection PhpUnhandledExceptionInspection */
        $refl = new \ReflectionProperty(__CLASS__, 'id');
        $refl->setAccessible(true);
        $refl->setValue($userO, $user['id']);
        $refl->setAccessible(false);
        $userO->setEmail($user['email']);
        if (array_key_exists('first_name', $user)) {
            $userO->setFirstName($user['first_name']);
        }
        if (array_key_exists('last_name', $user)) {
            $userO->setLastName($user['last_name']);
        }

        if (array_key_exists('roles', $user) && in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            $userO->isSuperAdmin = true;
        }

        return $userO;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}
