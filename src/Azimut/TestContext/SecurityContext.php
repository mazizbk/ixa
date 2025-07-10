<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-12-10
 */

namespace Azimut\TestContext;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;

class SecurityContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize contexts
    }

        /**
     * @Transform /^(\d+)$/
     */
/*    public function castStringToUser($string)
    {   //TODO

        return ;
    }
*/
    /**
     * @Given /^I fill in username "([^"]*)"$/
     */
/*/*    public function iFillInUsername($arg1)
    {
        throw new PendingException();
    }
*/
    /**
     * @Given /^I fill in password "([^"]*)"$/
     */
/*    public function iFillInPassword($arg1)
    {
        throw new PendingException();
    }
*/
}
