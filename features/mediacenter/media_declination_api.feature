# Mediacenter Media Declination API testing
#
# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-10-31
#
# Needed for testing :
#   a media declination with id 4, a media declination with id 5
#   a media image declination "My jpg image" with id 1
#   a media image declination "My image declination 1" with id 2 in media 4
#   a media image declination "My image declination 2" with id 3 in media 4
#   a media image declination "My image declination 3" with id 4 in media 5

Feature: Testing the Media REST API
    As an administrator
    I can use the api

    #TODO : set background
    #Background: I am login in as "???"

    Scenario: request all medias declinations
        Given a media with id "1"
        And a media declination named "My jpg image" with id "1" in media "1"
        When I send a GET request on "/api/mediacenter/mediadeclinations"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declinations" should exists
        And the JSON node "media_declinations[0].name" should be equal to "My jpg image"
        And the JSON node "media_declinations[0].media" should exists
        And the JSON node "media_declinations[0].media.id" should be equal to "1"

    Scenario: request a media image declination
        Given a media declination of type "image" named "My jpg image" with id "1"
        When  I send a GET request on "/api/mediacenter/mediadeclinations/1"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My jpg image"
        And the JSON node "media_declination.media_declination_type" should be equal to "image"

    Scenario: request a non existing media declination
        Given a media declination with id "99999" that does not exist
        When I send a GET request on "/api/mediacenter/mediadeclinations/99999"
        Then the response status code should be 404

    Scenario: create new media image declination
        Given a media with id "1"
        When I send a POST request on "/api/mediacenter/mediadeclinations" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new media declination |
            | media_declination[media]                               | 1                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2400                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new media declination"
        And the JSON node "media_declination.pixel_width" should exists
        And the JSON node "media_declination.pixel_width" should be equal to "2400"

    Scenario: create a media declination with an existing media declination name in the same media
        Given a media with id "1"
        And a media declination named "My new media declination" in media "1"
        When I send a POST request on "/api/mediacenter/mediadeclinations" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new media declination |
            | media_declination[media]                               | 1                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2400                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new media declination (1)"
        When I send a POST request on "/api/mediacenter/mediadeclinations" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new media declination |
            | media_declination[media]                               | 1                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2400                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new media declination (2)"


    Scenario: create a media declination with an existing media name in another media
        Given a media with id "1"
        And a media declination named "My new media declination" in media "1"
        And a media with id "2"
        When I send a POST request on "/api/mediacenter/mediadeclinations" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new media declination |
            | media_declination[media]                               | 2                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2400                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new media declination"

    Scenario: update an existing media image declination
        Given a media declination named "My jpg image" with id "1"
        When I send a PUT request on "/api/mediacenter/mediadeclinations/1" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new declination name  |
            | media_declination[media]                               | 2                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2800                     |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new declination name"
        And the JSON node "media_declination.media.id" should be equal to "2"
        And the JSON node "media_declination.pixel_width" should be equal to "2800"

    Scenario: update an existing media declination without all params
        Given a media declination named "My gif image" with id "3" and pixel width "200"
        When  I send a PUT request on "/api/mediacenter/mediadeclinations/3" with parameters:
            | key                           | value                        |
            | media_declination[name]       | My new declination name test |
            | media_declination[media]      | 1                            |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination.name" should be equal to "My new declination name test"
        When I send a GET request on "/api/mediacenter/mediadeclinations/3"
        Then the JSON node "media_declination.pixel_width" should not exists

    Scenario: update a non existing media image declination
        Given a media declination with id "99999" that does not exist
        When I send a PUT request on "/api/mediacenter/mediadeclinations/99999" with parameters:
            | key                                                    | value                    |
            | media_declination[name]                                | My new media declination |
            | media_declination[media]                               | 2                        |
            | media_declination[type]                                | image                    |
            | media_declination[media_declination_type][pixel_width] | 2400                     |
        Then the response status code should be 404

    #TODO : fix error bubbling and uncomment this
    #Scenario: update a media image declination and set a non existing media as parent
    #    Given a media declination with id "99999" that does not exist
    #    And a media declination with id "1"
    #    When I send a PUT request on "/api/mediacenter/mediadeclinations/1" with parameters:
    #        | key                                                    | value                    |
    #        | media_declination[name]                                | My new media declination |
    #        | media_declination[media]                               | 99999                    |
    #        | media_declination[type]                                | image                    |
    #        | media_declination[media_declination_type][pixel_width] | 2400                     |
    #    Then the response status code should be 400
    #    And the response should be in JSON
    #    And the JSON node "form" should exists
    #    And the JSON node "form.children.media.errors" should exists


    Scenario: update a media declination with a existing media name in the same media
        Given a media with id "1"
        And a media declination named "My png image" in media "2"
        And a media declination with id "1" in media "2"
        When I send a PUT request on "/api/mediacenter/mediadeclinations/1" with parameters:
            | key                                                    | value                        |
            | media_declination[name]                                | My png image |
            | media_declination[media]                               | 2                            |
            | media_declination[type]                                | image                        |
            | media_declination[media_declination_type][pixel_width] | 2400                         |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My png image (1)"

    Scenario: update a media declination with a existing media name in another media
        Given a media with id "1"
        And a media declination named "My new declination name test" in media "1"
        And a media with id "2"
        When I send a PUT request on "/api/mediacenter/mediadeclinations/1" with parameters:
            | key                                                    | value                   |
            | media_declination[name]                                | My new declination name |
            | media_declination[media]                               | 2                       |
            | media_declination[type]                                | image                   |
            | media_declination[media_declination_type][pixel_width] | 2400                    |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My new declination name"


    Scenario: partialy update an existing media image declination
        Given a media declination named "My new declination name" with id "1" and pixel width "2400"
        When I send a PATCH request on "/api/mediacenter/mediadeclinations/1" with parameters:
            | key                               | value              |
            | media_declination[name]           | My declination 111 |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My declination 111"
        And the JSON node "media_declination.pixel_width" should be equal to "2400"


    Scenario: partialy update an existing media image declination with method override
        Given a media declination named "My declination 111" with id "1" and pixel width "2400"
        When I send a POST request on "/api/mediacenter/mediadeclinations/1" with parameters:
            | key                               | value              |
            | media_declination[name]           | My declination 112 |
            | _method                           | PATCH              |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "media_declination" should exists
        And the JSON node "media_declination.name" should be equal to "My declination 112"
        And the JSON node "media_declination.pixel_width" should be equal to "2400"

    Scenario: delete a media declination
        Given a media declination with id "1"
        When I send a DELETE request on "/api/mediacenter/mediadeclinations/1"
        Then the response status code should be 204
        When I send a GET request on "/api/mediacenter/mediadeclinations/1"
        Then the response status code should be 404

    Scenario: delete a non existing media declination
        Given a media declination with id "99999" that does not exist
        When I send a DELETE request on "/api/mediacenter/mediadeclinations/99999"
        Then the response status code should be 404
