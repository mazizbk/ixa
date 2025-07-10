# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-12-06 15:07:58

Feature: Testing the CmsFile REST API
    As an administrator
    I can use the api

    #TODO : set background
    #Background: I am login in as "???"

    Scenario: request all cms_files
        Given a cms_file of type "article" with id "1" and title "my article 1"
        When I send a GET request on "/api/cms/cmsfiles"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_files" should exists
        And the JSON node "cms_files[0].name" should be equal to "my article 1"
        And the JSON node "cms_files[0].cms_file_type" should be equal to "article"

     Scenario: request a cms_file article
        Given a cms_file of type "article" with id "1" and title "my article 1"
        When I send a GET request on "/api/cms/cmsfiles/1"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_file" should exists
        And the JSON node "cms_file.title" should be equal to "my article 1"
        And the JSON node "cms_file.cms_file_type" should be equal to "article"

    Scenario: request a non existing cms_file
        Given a cms_file with id "99999" that does not exist
        When I send a GET request on "/api/cms/cmsfiles/99999"
        Then the response status code should be 404


    Scenario: create new cms_file article
        Given I send a POST request on "/api/cms/cmsfiles" with parameters:
            | key                             | value                   |
            | cms_file[type]                  | article                 |
            | cms_file[cms_file_type][title]  | my new cms article test |
            | cms_file[cms_file_type][text]   | my text                 |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_file" should exists
        And the JSON node "cms_file.title" should be equal to "my new cms article test"
        And the JSON node "cms_file.text" should be equal to "my text"

    Scenario: update an existing cms_file article
        Given a cms_file of type "article" with id "1" and title "my article 1"
        When I send a PUT request on "/api/cms/cmsfiles/1" with parameters:
            | key                             | value                  |
            | cms_file[type]                  | article                |
            | cms_file[cms_file_type][author] | Dupont                 |
            | cms_file[cms_file_type][title]  | my article 1 new title |
            | cms_file[cms_file_type][text]   | my new text            |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_file" should exists
        And the JSON node "cms_file.title" should be equal to "my article 1 new title"
        And the JSON node "cms_file.text" should be equal to "my new text"

    #Scenario: update an existing cms_file with its declinations

    Scenario: update an existing cms_file article without all params
        Given a cms_file of type "article" with id "2" and title "my article 2" and author "Jean Lorem"
        When I send a PUT request on "/api/cms/cmsfiles/2" with parameters:
            | key                             | value                  |
            | cms_file[cms_file_type][title]  | my article 2 new title |
        Then the response status code should be 200
        And the JSON node "cms_file.title" should be equal to "my article 2 new title"
        When I send a GET request on "/api/cms/cmsfiles/2"
        Then the JSON node "cms_file.author" should not exists

    Scenario: update a non existing cms_file article
        Given a cms_file with id "99999" that does not exist
        When I send a PUT request on "/api/cms/cmsfiles/99999" with parameters:
            | key                             | value                   |
            | cms_file[type]                  | article                 |
            | cms_file[cms_file_type][title]  | my new cms article test |
            | cms_file[cms_file_type][text]   | my text                 |
        Then the response status code should be 404

    Scenario: partialy update an existing cms_file article
        Given a cms_file of type "article" with id "1" and author "Dupont"
        When I send a PATCH request on "/api/cms/cmsfiles/1" with parameters:
            | key                             | value                   |
            | cms_file[type]                  | article                 |
            | cms_file[cms_file_type][title]  | my test title           |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_file" should exists
        And the JSON node "cms_file.author" should be equal to "Dupont"

    Scenario: partialy update an existing cms_file article with method override
        Given a cms_file of type "article" with id "1" and author "Dupont"
        When I send a POST request on "/api/cms/cmsfiles/1" with parameters:
            | key                             | value                   |
            | cms_file[type]                  | article                 |
            | cms_file[cms_file_type][title]  | my test title           |
            | _method                         | PATCH                   |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "cms_file" should exists
        And the JSON node "cms_file.author" should be equal to "Dupont"



    Scenario: delete a cms_file (with attachments)
        Given a cms_file with id "1" that has attachmentss
        When I send a DELETE request on "/api/cms/cmsfiles/1"
        Then the response status code should be 204
        When I send a GET request on "/api/cms/cmsfiles/1"
        Then the response status code should be 404

    Scenario: delete a non existing cms_file
        Given a cms_file with id "99999" that does not exist
        When I send a DELETE request on "/api/cms/cmsfiles/99999"
        Then the response status code should be 404
