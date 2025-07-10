<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Azimut\Behat\KernelExtension\KernelFactory;
use Behat\MinkExtension\Context\MinkContext;
use Azimut\TestContext\RestContext;
use Azimut\TestContext\VariableContext;
use Azimut\TestContext\MediacenterContext;
use Azimut\TestContext\CmsContext;
use Azimut\TestContext\SecurityContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize context
        $this->useContext('RestContext', new RestContext($parameters));
        $this->useContext('VariableContext', new VariableContext($parameters));

        $this->useContext('MediacenterContext', new MediacenterContext($parameters));
        $this->useContext('CmsContext', new CmsContext($parameters));

        $this->useContext('SecurityContext', new SecurityContext($parameters));

    }

    /**
     * @Given /^I wait for (\d+) seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
         $this->getSession()->wait($seconds*1000);
    }

    /**
     * @Then /^I wait for the folder tree to appear$/
     */
    public function iWaitForTheFolderTreeToAppear()
    {
        $this->getSession()->wait(5000,
            "$('#folderTree').children().length > 0"
        );
    }

    /**
    * Use this for link without href
    *
    * @When /^I click on the element "([^"]*)"$/
    */
    public function iClickOnTheElement($selector)
    {

        $session = $this->getSession();
        /*$element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '*//*[text()="'. $text .'"]')
        );*/


        $element = $this->getSession()
                      ->getPage()
                      ->find("css", $selector)
                      ->click();

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find element from css selector: "%s"', $selector));
        }

        $element->click();

    }

    /**
     * @Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visit('/login');
        $this->fillField('username', $email);
        $this->fillField('password', $password);
        $this->pressButton('Login');

        /* Scenario: Logging in as admin
        Given I am on "/admin/user/login"
        When I fill in "username" with "admin@admin.net"
        And I fill in "password" with "admin"
        And I press "_submit"
        Then I should be on "/admin/"
        */

    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }
//
}
