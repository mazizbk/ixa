<?php

namespace Azimut\TestContext;

use Behat\Behat\Context\BehatContext;

/**
 * This class is a simple service allowing to store and replace variables later.
 *
 * To start using it, from a different context class:
 *
 *     $this->getMainContext()->getSubContext('VariableContext')->setVariable('id', 32);
 *
 * And if you need to replace variables:
 *
 *     $this->getMainContext()->getSubContext('VariableContext')->replaceVariables('/path/to/%id%');
 *
 * So you can create two steps like this:
 *
 * public function iReadIdInResponse()
 * {
 *     $this->getMainContext()->getSubContext('VariableContext')->setVariable('id', $this->response['id']);
 * }
 *
 * public function iAmOn($path)
 * {
 *     $path = $this->getMainContext()->getSubContext('VariableContext')->replaceVariables($path);
 *     // call mink to open path
 * }
 *
 * And your test would be:
 *
 *    Given I am on "/editorial/active"
 *      And I read ID in response
 *     When I am on "/editorial/%id%"
 *     Then I should see "active"
 */
class VariableContext extends BehatContext
{
    protected $replacements = array();

    public function setVariable($name, $value)
    {
        $this->replacements['%'.$name.'%'] = $value;
    }

    public function replaceVariables($text)
    {
        return strtr($text, $this->replacements);
    }
}
