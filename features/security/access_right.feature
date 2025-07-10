# @author: Gerda Le Duc <gerda.leduc@azimut.net>
# date:   2013-12-12

Feature: Security Access Right
    In order to maintain rights on objects
    As admin i have different rights from a simple user


    Scenario: Log in as Admin
        Given I am logged in as "admin@admin.net" with password "admin"
        And I am on "/"
        Then I should see "Hello World!"

    Scenario: Logged in as Admin I can acess /admin
        Given I am logged in as "admin@admin.net" with password "admin"
        And I am on "/admin/"
        Then I should see "Dashboard"

    Scenario: Log in as User
        Given I am logged in as "user@user.net" with password "user"
        And I am on "/"
        Then I should see "Hello World!"

    Scenario: Logged in as User I can't acess /admin
        Given I am logged in as "user@user.net" with password "user"
        When I am on "/admin/"
        Then the response status code should be 403
