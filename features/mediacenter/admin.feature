# @author: Yoann Le Crom <yoann.lecrom@azimut.net>
# date:   2013-10-24

Feature: MediaCenter Admin
 	As an administrator
 	I can manage folders and medias

 	@mink:selenium2
 	Scenario: View folder tree
		Given I am on "/admin/mediacenter/"
		And I wait for the folder tree to appear
	  	Then I should see "my folder 1"
	  	Then I should see "my folder 2"

	#TODO : complete this test
	@mink:selenium2
	Scenario: View folder content
		Given I am on "/admin/mediacenter/#/files/my-folder-1"
		And I wait for 3 seconds
		Then I should see "Create"

	#TODO : write the other tests
