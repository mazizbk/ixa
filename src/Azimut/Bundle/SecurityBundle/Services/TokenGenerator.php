<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-04-29 12:05:00
 */

namespace Azimut\Bundle\SecurityBundle\Services;

class TokenGenerator
{
    public function generateToken()
    {
        return rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');
    }

    private function getRandomNumber()
    {
        return hash('sha256', uniqid(mt_rand(), true), true);
    }
}
