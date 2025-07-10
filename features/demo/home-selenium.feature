Feature: Home
  In order to test app
  As a developer
  I need to be able to reach the Hello World home page

  @mink:selenium2
  Scenario: Homepage
	  Given I am on "/"
	  Then I should see "Hello World"
