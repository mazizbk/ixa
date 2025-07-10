<?php
/**
 * Created by mikaelp on 05-Sep-18 5:21 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

/**
 * Types implementing this interface must have a `type` option that will be
 * set to `create` or `update` depending on the context
 * @see \Azimut\Bundle\MontgolfiereAppBundle\Controller\AbstractBackofficeEntityController
 * @see \Azimut\Bundle\MontgolfiereAppBundle\Controller\AbstractBackofficeSubEntityController
 */
interface HasTypeOption
{

}
