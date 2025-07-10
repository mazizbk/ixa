<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-25
 */
//source : https://github.com/tentacode/BehatCH
//source : https://github.com/sanpii/behatch-contexts


namespace Azimut\TestContext;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_ExpectationFailedException as AssertException;

class RestContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
        $this->useContext('JsonContext', new JsonContext($parameters));
        $this->useContext('VariableContext', new VariableContext($parameters));
    }

  /**
   * Shortcut for retrieving Mink context
   *
   * @return \Behat\Mink\Behat\Context\MinkContext
   */
  public function getMinkContext()
  {
      return $this->getMainContext();
  }

  /**
   * Sends a HTTP request
   *
   * @Given /^I send a (GET|POST|PUT|PATCH|DELETE|OPTION) request on "([^"]*)"$/
   */
  public function iSendARequestOn($method, $url)
  {

    //replace variables in url from context
    $url = $this->getMainContext()->getSubContext('VariableContext')->replaceVariables($url);

      $client = $this->getMinkContext()->getSession()->getDriver()->getClient();

    // intercept redirection
    $client->followRedirects(false);
      $client->request($method, $this->getMinkContext()->locatePath($url));
      $client->followRedirects(true);
  }

  /**
   * Sends a HTTP request with a some parameters
   *
   * @Given /^I send a (GET|POST|PUT|PATCH|DELETE|OPTION) request on "([^"]*)" with parameters:$/
   */
  public function iSendARequestOnWithParameters($method, $url, TableNode $datas)
  {
      //use variable context to inject varaible content
    //$url = $this->getContext('VariableContext')->replaceVariables($url);


    $client = $this->getMinkContext()->getSession()->getDriver()->getClient();

    // do not intercept redirection
    $client->followRedirects(true);

      $parameters = array();
      foreach ($datas->getHash() as $row) {
          if (!isset($row['key']) || !isset($row['value'])) {
              throw new \Exception(sprintf("You must provide a 'key' and 'value' column in your table node."));
          }
          $parameters[$row['key']] = $row['value'];
      }

      $client->request($method, $this->getMinkContext()->locatePath($url), $parameters);
    //$client->followRedirects(true);
  }

  /**
   * Sends a HTTP request with a body
   *
   * @When /^I send a (GET|POST|PUT|DELETE|OPTION) request on "([^"]*)" with body:$/
   */
  public function iSendARequestOnWithBody($method, $url, PyStringNode $body)
  {
      $client = $this->getMinkContext()->getSession()->getDriver()->getClient();

    // intercept redirection
    $client->followRedirects(false);

      $client->request($method, $this->getMinkContext()->locatePath($url), array(), array(), array(), $body->getRaw());
      $client->followRedirects(true);
  }

  /**
   * Checks, whether the response content is equal to given text
   *
   * @Given /^the response should be equal to:$/
   */
  public function theResponseShouldBeEqualTo(PyStringNode $expected)
  {
      $expected = str_replace('\\"', '"', $expected);
      $actual   = $this->getMinkContext()->getSession()->getPage()->getContent();

      try {
          assertEquals($expected, $actual);
      } catch (AssertException $e) {
          $message = sprintf('The string "%s" is not equal to the response of the current page', $expected);
          throw new \Behat\Mink\Exception\ExpectationException($message, $this->getMinkContext()->getSession(), $e);
      }
  }

  /**
   * Checks, whether the header name is equal to given text
   *
   * @Given /^the header "([^"]*)" should be equal to "([^"]*)"$/
   */
  public function theHeaderShouldBeEqualTo($name, $expected)
  {
      $header = $this->getMinkContext()->getSession()->getResponseHeaders();

      try {
          if (!isset($header[$name])) {
              throw new \Exception(sprintf('The "%s" header do not exist'));
          }
          assertEquals($expected, $header[$name]);
      } catch (AssertException $e) {
          $message = sprintf('The header "%s" is not equal to "%s"', $name, $expected);
          throw new \Behat\Mink\Exception\ExpectationException($message, $this->getMinkContext()->getSession(), $e);
      }
  }

  /**
   * Add an header element in a request
   *
   * @Given /^I add "([^"]*)" header equal to "([^"]*)"$/
   */
  public function iAddHeaderEqualTo($name, $value)
  {
      $this->getMinkContext()->getSession()->getDriver()->getClient()->setServerParameter($name, $value);
  }
}
