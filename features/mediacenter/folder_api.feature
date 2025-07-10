# Mediacenter Folder API testing
#
# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-10-24

Feature: Testing the Folder REST API
    As an administrator
    I can use the api

    #TODO : set background
    #Background: I am login in as "???"

    Scenario: request all folders
        Given a folder named "My library 1" with id "1"
        When I send a GET request on "/api/mediacenter/folders"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folders" should exists
        And the JSON node "folders[0].name" should be equal to "My library 1"

    Scenario: request a folder
        Given a folder named "My library 1" with id "1"
        When I send a GET request on "/api/mediacenter/folders/1"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My library 1"

    Scenario: request a non existing folder
        Given a folder with id "99999" that does not exist
        When I send a GET request on "/api/mediacenter/folders/99999"
        Then the response status code should be 404


    Scenario: create new folder
        Given a folder with id "1"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | My new test folder |
            | folder[parent_folder] | 1                  |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new test folder"
        #Check that the folder has been properly created
        Then I read FOLDER_ID in response
        When I send a GET request on "/api/mediacenter/folders/%FOLDER_ID%"
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new test folder"

    Scenario: create new folder with an existing folder name in the same folder
        Given a folder with id "1"
        And a folder named "My new test folder" subfolder of "1"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | My new test folder |
            | folder[parent_folder] | 1                  |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new test folder (1)"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | My new test folder |
            | folder[parent_folder] | 1                  |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new test folder (2)"

    Scenario: create new folder with an existing media name in the same folder
        Given a folder with id "3"
        And a media named "Media image test 1" in folder "3"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | Media image test 1 |
            | folder[parent_folder] | 3                  |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Media image test 1 (1)"

    Scenario: create new folder with an existing folder name in another folder
        Given a folder with id "1"
        And a folder with id "3"
        And a folder named "My new test folder" subfolder of "1"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | My new test folder |
            | folder[parent_folder] | 3                  |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new test folder"

     Scenario: create new folder with an existing media name in another folder
        Given a folder with id "1"
        And a folder with id "3"
        And a media named "Media image test 1" in folder "3"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | Media image test 1 |
            | folder[parent_folder] | 1                  |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Media image test 1"

    Scenario: create a new folder in a non existing one
        Given a folder with id "99999" that does not exist
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value              |
            | folder[name]          | My new test folder |
            | folder[parent_folder] | 99999               |
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "form" should exists
        And the JSON node "form.children.parent_folder.errors" should exists

    Scenario: create a new root folder
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value                  |
            | folder[name]          | My new root folder     |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new root folder"
        And the JSON node "folder.parent_folder_id" should not exists

    Scenario: create a new root folder with an existing name
        Given a root folder named "My new root folder"
        When I send a POST request on "/api/mediacenter/folders" with parameters:
            | key                   | value                  |
            | folder[name]          | My new root folder     |
        Then the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My new root folder (1)"
        And the JSON node "folder.parent_folder_id" should not exists



    Scenario: update an existing folder
        Given a folder named "My library 2" with id "2"
        When I send a PUT request on "/api/mediacenter/folders/2" with parameters:
            | key                   | value                   |
            | folder[name]          | My library 2 new name    |
            | folder[parent_folder] | 1                       |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My library 2 new name"
        And the JSON node "folder.parent_folder_id" should be equal to "1"

    Scenario: update an existing folder with an existing folder name
        Given a folder named "My folder 1" subfolder of "1"
        And a folder with id "4" subfolder of "1"
        When I send a PUT request on "/api/mediacenter/folders/4" with parameters:
            | key                   | value              |
            | folder[name]          | My folder 1     |
            | folder[parent_folder] | 1                  |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My folder 1 (1)"

    Scenario: update an existing root folder with an existing folder name
        Given a root folder named "My library 1"
        And a root folder with id "5"
        When I send a PUT request on "/api/mediacenter/folders/5" with parameters:
            | key                   | value              |
            | folder[name]          | My library 1       |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "My library 1 (1)"

    Scenario: update an existing folder with an existing media name in the same folder
        Given a folder with id "3"
        And a folder with id "7" subfolder of "3"
        And a media named "Media video test 1" in folder "3"
        When I send a PUT request on "/api/mediacenter/folders/7" with parameters:
            | key                   | value              |
            | folder[name]          | Media video test 1 |
            | folder[parent_folder] | 3                  |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Media video test 1 (1)"

    Scenario: update an existing folder with an existing media name in another folder
        Given a folder with id "5"
        And a folder with id "6" subfolder of "5"
        And a folder with id "3"
        And a media named "Media video test 1" in folder "3"
        When I send a PUT request on "/api/mediacenter/folders/6" with parameters:
            | key                   | value              |
            | folder[name]          | Media video test 1 |
            | folder[parent_folder] | 5                  |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Media video test 1"

    Scenario: update an existing folder without all params
        Given a folder named "My folder 1 (1)" with id "4"
        When I send a PUT request on "/api/mediacenter/folders/4" with parameters:
            | key                   | value                   |
            | folder[parent_folder] | 1                       |
        Then the response status code should be 200
        And the JSON node "folder.parent_folder_id" should be equal to "1"
        When I send a GET request on "/api/mediacenter/folders/4"
        Then the JSON node "folder.name" should be equal to ""

    Scenario: update a non existing folder
        Given a folder with id "99999" that does not exist
        When I send a PUT request on "/api/mediacenter/folders/99999" with parameters:
            | key                   | value                         |
            | folder[name]          | My unexisting folder new name |
            | folder[parent_folder] | 2                             |
        Then the response status code should be 404

    Scenario: update a folder and set a non existing one as parent
        Given a folder with id "2"
        And a folder with id "99999" that does not exist
        When I send a PUT request on "/api/mediacenter/folders/2" with parameters:
            | key                   | value               |
            | folder[name]          | My new subfolder 11 |
            | folder[parent_folder] | 99999                |
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "form" should exists
        And the JSON node "form.children.parent_folder.errors" should exists

    Scenario: partialy update an existing folder (move folder)
        Given a folder with id "1"
        And a folder named "My library 2 new name" with id "2" subfolder of "1"
        And a folder with id "5"
        When  I send a PATCH request on "/api/mediacenter/folders/2" with parameters:
            | key                   | value                     |
            | folder[parent_folder] | 5                         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.parent_folder_id" should be equal to "5"
        And the JSON node "folder.name" should be equal to "My library 2 new name"

    Scenario: partialy update an existing folder (rename folder)
        Given a folder named "My library 2 new name" with id "2" subfolder of "5"
        When I send a PATCH request on "/api/mediacenter/folders/2" with parameters:
            | key          | value                     |
            | folder[name] | Folder new name           |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Folder new name"
        And the JSON node "folder.parent_folder_id" should be equal to "5"

    Scenario: partialy update an existing folder with method override
        Given a folder named "Folder new name" with id "2" subfolder of "5"
        When I send a POST request on "/api/mediacenter/folders/2" with parameters:
            | key          | value                     |
            | folder[name] | Folder new name 2         |
            | _method      | PATCH                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "folder" should exists
        And the JSON node "folder.name" should be equal to "Folder new name 2"
        And the JSON node "folder.parent_folder_id" should be equal to "5"

    Scenario: delete a folder that has no medias and no subfolder
        Given a folder with id "4"
        And a folder with id "4" that has no subfolders
        And a folder with id "4" that has no medias
        When I send a DELETE request on "/api/mediacenter/folders/4"
        Then the response status code should be 204
        When I send a GET request on "/api/mediacenter/folders/4"
        Then the response status code should be 404

    Scenario: delete a folder that has subfolders but no medias
        Given a folder with id "5"
        And a folder with id "5" that has subfolders
        And a folder with id "5" that has no medias
        When I send a DELETE request on "/api/mediacenter/folders/5"
        Then the response status code should be 204
        When I send a GET request on "/api/mediacenter/folders/5"
        Then the response status code should be 404
        When I send a GET request on "/api/mediacenter/folders/6"
        Then the response status code should be 404

    Scenario: delete a non existing folder
        Given a folder with id "99999" that does not exist
        When I send a DELETE request on "/api/mediacenter/folders/99999"
        Then the response status code should be 404

    Scenario: delete a folder that has subfolders and medias (with declinations)
        Given a folder with id "1"
        And a folder with id "1" that has subfolders
        And a folder with id "3" subfolder of "1"
        And a folder with id "3" that has medias
        And a media with id "1" in folder "3"
        And a media with id "1" that has declinations
        When I send a DELETE request on "/api/mediacenter/folders/1"
        Then the response status code should be 204
        When I send a GET request on "/api/mediacenter/folders/1"
        Then the response status code should be 404
