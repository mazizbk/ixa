# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-10-24

Feature: Admin Dashboard
  In order to test backoffice dashboard
  As an administrator
  I need to be able to reach the dashboard page

  Scenario: Dashboard
	  Given I am on "/admin/"
	  Then I should see "Dashboard"
