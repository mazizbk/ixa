<?php
/**
 * Created by mikaelp on 2018-11-12 11:37 AM
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Exception;


use Symfony\Component\Security\Core\Exception\AccountStatusException;

class EmailNotConfirmedException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Please validate your email address before logging in.';
    }
}
