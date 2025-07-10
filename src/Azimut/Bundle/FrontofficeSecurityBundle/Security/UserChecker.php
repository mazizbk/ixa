<?php
/**
 * Created by mikaelp on 2018-11-12 11:35 AM
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Security;


use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\FrontofficeSecurityBundle\Exception\EmailNotConfirmedException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        if(!$user instanceof FrontofficeUser) {
            return;
        }

        if(!$user->isActive()) {
            throw new DisabledException();
        }

        if(!$user->isEmailConfirmed()) {
            throw new EmailNotConfirmedException();
        }
    }
}
