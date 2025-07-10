<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 17:11:23
 */

namespace Azimut\Bundle\ShopBundle\Service;

/**
 * This is a demo simplified payment service
 */
class DemoSimplePaymentService
{
    /**
     * Request a payment
     * @param  integer $amount
     * @param  string  $cardNumber
     * @return bool    Return true if payment accepted
     */
    public function requestPayment($amount, $cardNumber)
    {
        if ('0001000100010001' == $cardNumber) {
            return true;
        }
        return false;
    }
}
