<?php

/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-04 09:42:05
 */

namespace Azimut\Bundle\FrontofficeBundle\Exception;

class WrongFirewallException extends \RuntimeException implements ExceptionInterface
{
    public function __construct($msg = '')
    {
        $msg = $msg ?: 'Current firewall is not "frontoffice"';
        parent::__construct($msg);
    }
}
