# Mediacenter Media API testing
#
# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-10-29

Feature: Testing the Media REST API
    As an administrator
    I can use the api

    #TODO : set background
    #Background: I am login in as "???"

    Scenario: request all medias
        Given a media of type "image" named "Media image test 1" with id "1" in folder "3"
        When I send a GET request on "/api/mediacenter/medias?locale=en"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "medias" should exists
        And the JSON node "medias[0].name" should be equal to "Media image test 1"
        And the JSON node "medias[0].media_type" should exists
        And the JSON node "medias[0].media_type" should be equal to "image"
        And the JSON node "medias[0].folder_id" should exists
        And the JSON node "medias[0].folder_id" should be equal to "3"

     Scenario: request a media image
        Given a media of type "image" named "Media image test 1" with id "1" and alt_text "This is an alternate text"
        When I send a GET request on "/api/mediacenter/media/1?locale=en"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "Media image test 1"
        And the JSON node "media.media_type" should be equal to "image"
        And the JSON node "media.alt_text" should be equal to "This is an alternate text"

    Scenario: request a media video
        Given a media of type "video" named "Media video test 1" with id "4" and copyright "Robert Ipsum"
        When I send a GET request on "/api/mediacenter/media/4?locale=en"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "Media video test 1"
        And the JSON node "media.media_type" should be equal to "video"
        And the JSON node "media.copyright" should be equal to "Robert Ipsum"

    Scenario: request a non existing media
        Given a media with id "99999" that does not exist
        When I send a GET request on "/api/mediacenter/media/99999?locale=en"
        Then the response status code should be 404


    Scenario: create new media image (with first declination)
        Given a folder with id "1"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My new test media image  |
            | media[locale]                      | en                       |
            | media[folder]                      | 1                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media image"
        And the JSON node "media.media_type" should be equal to "image"
        And the JSON node "media.alt_text" should be equal to "My alt text"
        And the JSON node "media.declinations[0].name" should be equal to "My new image declination"
        And the JSON node "media.declinations[0].media_declination_type" should be equal to "image"

    Scenario: create new media video (with first declination)
        Given a folder with id "1"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My new test media video  |
            | media[locale]                      | en                       |
            | media[folder]                      | 1                        |
            | media[type]                        | video                    |
            | media[media_type][copyright]       | My copyright             |
            | media[media_declinations][0][name] | My new video declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media video"
        And the JSON node "media.media_type" should be equal to "video"
        And the JSON node "media.copyright" should be equal to "My copyright"
        And the JSON node "media.declinations[0].name" should be equal to "My new video declination"
        And the JSON node "media.declinations[0].media_declination_type" should be equal to "video"

    Scenario: create a media with an existing media name in the same folder
        Given a folder with id "1"
        And a media named "My new test media image" in folder "1"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My new test media image  |
            | media[locale]                      | en                       |
            | media[folder]                      | 1                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media image (1)"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My new test media image  |
            | media[locale]                      | en                       |
            | media[folder]                      | 1                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media image (2)"

    Scenario: create a media with an existing media name in another folder
        Given a folder with id "1"
        And a media named "My new test media image" in folder "1"
        And a folder with id "2"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My new test media image  |
            | media[locale]                      | en                       |
            | media[folder]                      | 2                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media image"

    Scenario: create a media with an existing folder name in the same folder
        Given a folder with id "1"
        And a folder named "My folder 2" subfolder of "1"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My folder 2              |
            | media[locale]                      | en                       |
            | media[folder]                      | 1                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My folder 2 (1)"

    Scenario: create a media with an existing folder name in another folder
        Given a folder with id "1"
        And a folder named "My folder 2" subfolder of "1"
        And a folder with id "2"
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My folder 2              |
            | media[locale]                      | en                       |
            | media[folder]                      | 2                        |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My folder 2"

    Scenario: create a media without folder
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My media with no folder  |
            | media[locale]                      | en                       |
            | media[folder]                      |                          |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "form" should exists
        And the JSON node "form.children.folder.errors" should exists

    Scenario: create a media in a non existing folder
        Given a folder with id "99999" that does not exist
        When I send a POST request on "/api/mediacenter/media" with parameters:
            | key                                | value                    |
            | media[name]                        | My media                 |
            | media[locale]                      | en                       |
            | media[folder]                      | 99999                    |
            | media[type]                        | image                    |
            | media[media_type][alt_text]        | My alt text              |
            | media[media_declinations][0][name] | My new image declination |
            #TODO: attach file to upload
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "form" should exists
        And the JSON node "form.children.folder.errors" should exists

    #TODO: add scenarios for testing media creation in different locales


    Scenario: update an existing media image
        Given a folder with id "2"
        And a media of type "image" named "Media image test 1" with id "1" and alt_text "This is an alternate text"
        When I send a PUT request on "/api/mediacenter/media/1" with parameters:
            | key                         | value                   |
            | media[name]                 | My media image new name |
            | media[locale]               | en                      |
            | media[folder]               | 1                       |
            | media[type]                 | image                   |
            | media[media_type][alt_text] | My new alt text         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My media image new name"
        And the JSON node "media.folder.id" should be equal to "1"
        And the JSON node "media.alt_text" should be equal to "My new alt text"

    Scenario: update an existing media video without all params
        Given a media of type "video" named "Media video test 1" with id "4" and copyright "Robert Ipsum"
        And a folder with id "3"
        When I send a PUT request on "/api/mediacenter/media/4" with parameters:
            | key                   | value                    |
            | media[name]           | My media video new name  |
            | media[locale]         | en                       |
            | media[folder]         | 3                        |
        Then the response status code should be 200
        And the JSON node "media.name" should be equal to "My media video new name"
        When I send a GET request on "/api/mediacenter/media/4"
        Then the JSON node "media.copyright" should not exists

    Scenario: update a non existing media image
        Given a media with id "99999" that does not exist
        When I send a PUT request on "/api/mediacenter/media/99999" with parameters:
            | key                         | value                     |
            | media[name]                 | My unexisting media image |
            | media[locale]               | en                        |
            | media[folder]               | 1                         |
            | media[type]                 | image                     |
            | media[media_type][alt_text] | My alt text               |
        Then the response status code should be 404

    #TODO : uncomment this when error bubbling will be fixed
    #Scenario: update a media image and set a non existing folder as parent
    #    Given a media with id "1"
    #    And a folder with id "99999" that does not exist
    #    When I send a PUT request on "/api/mediacenter/media/1" with parameters:
    #        | key                         | value                     |
    #        | media[name]                 | My media image            |
    #        | media[folder]               | 99999                     |
    #        | media[type]                 | image                     |
    #        | media[media_type][alt_text] | My alt text               |
    #    Then the response status code should be 400
    #    Then the response should be in JSON
    #    Then the JSON node "form" should exists
    #    And the JSON node "form.children.folder.errors" should exists

    Scenario: update a media with a existing media name in the same folder
        Given a folder with id "3"
        And a media of type "image" named "Media image test 2" with id "2" in folder "3"
        And a media named "Media image test 3" in folder "3"
        When I send a PUT request on "/api/mediacenter/media/2" with parameters:
            | key                          | value                   |
            | media[name]                  | Media image test 3      |
            | media[locale]                | en                      |
            | media[folder]                | 3                       |
            | media[type]                  | image                   |
            | media[media_type][alt_text]  | My new alt text         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "Media image test 3 (1)"

    Scenario: update a media with an existing media name in another folder
        Given a folder with id "3"
        And a folder with id "1"
        And a media of type "image" with id "2" in folder "3"
        And a media named "My new test media image" in folder "1"
        When I send a PUT request on "/api/mediacenter/media/2" with parameters:
            | key                         | value                   |
            | media[name]                 | My new test media image |
            | media[locale]               | en                      |
            | media[folder]               | 3                       |
            | media[type]                 | image                   |
            | media[media_type][alt_text] | My new alt text         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My new test media image"

    Scenario: update a media with an existing folder name in the same folder
        Given a folder with id "1"
        And a folder named "My folder 1" subfolder of "1"
        And a media of type "image" with id "1" in folder "1"
        When I send a PUT request on "/api/mediacenter/media/1" with parameters:
            | key                         | value                   |
            | media[name]                 | My folder 1             |
            | media[locale]               | en                      |
            | media[folder]               | 1                       |
            | media[type]                 | image                   |
            | media[media_type][alt_text] | My new alt text         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My folder 1 (1)"

    Scenario: update a media with an existing folder name in another folder
       Given a folder with id "1"
       And a folder with id "3"
       And a folder named "My folder 1" subfolder of "1"
       And a media of type "image" with id "2" in folder "3"
       When I send a PUT request on "/api/mediacenter/media/2" with parameters:
            | key                         | value                   |
            | media[name]                 | My folder 1             |
            | media[locale]               | en                      |
            | media[folder]               | 2                       |
            | media[type]                 | image                   |
            | media[media_type][alt_text] | My new alt text         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My folder 1"

    Scenario: partialy update an existing media image
        Given a media of type "image" named "Media image test 3" with id "3" in folder "3"
        When I send a PATCH request on "/api/mediacenter/media/3" with parameters:
            | key                   | value             |
            | media[name]           | My media new name |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My media new name"
        And the JSON node "media.folder.id" should be equal to "3"

    Scenario: partialy update an existing media image with method override
        Given a media of type "image" named "My media new name" with id "3" in folder "3"
        When I send a POST request on "/api/mediacenter/media/3" with parameters:
            | key                   | value               |
            | media[name]           | My media new name 2 |
            | _method               | PATCH               |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media" should exists
        And the JSON node "media.name" should be equal to "My media new name 2"
        And the JSON node "media.folder.id" should be equal to "3"

    #TODO: add scenarios for testing media creation in different locales


    Scenario: delete a media (with declination)
        Given a media with id "1" that has declinations
        When I send a DELETE request on "/api/mediacenter/media/1"
        Then the response status code should be 204
        When I send a GET request on "/api/mediacenter/media/1?locale=en"
        Then the response status code should be 404

    Scenario: delete a non existing media
        Given a media with id "99999" that does not exist
        When I send a DELETE request on "/api/mediacenter/media/99999"
        Then the response status code should be 404
