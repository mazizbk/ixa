/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:14:11
 */

'use strict';

angular.module('azimutCmsContact.controller')

.controller('CmsContactMainController',[
'$log', '$scope','$state', 'NotificationService', 'CmsFileFactory',
function($log, $scope, $state, NotificationService, CmsFileFactory) {
    $log = $log.getInstance('CmsContactMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if(!CmsFileFactory.isGrantedUser()) {
        $log.warn("User has not access to CmsFileFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'cms_contact'});
        return;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('cms_contact.meta.title'));

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    // clear notification at each state change
    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    // show loader in main content panel
    // set this to true if the content is loaded from this controller
    // leave it to false if the content was preloader before calling controller
    $scope.isMainContentLoading = false;

    // set function to allow easy update of loading state in children scopes
    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    // retrieve data from your entity factory like for example:
    $scope.contacts = CmsFileFactory.files();

    // when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    $scope.openContact = function(contact) {
        $state.go('backoffice.cmscontact.contact_detail', {file_id: contact.id});
    };

    $scope.deleteContact = function(contact) {
        CmsFileFactory.deleteFile(contact).then(function (response) {
            $log.info("Contact has been deleteed", contact);
            $state.go('backoffice.cmscontact');
            NotificationService.addSuccess(Translator.trans('notification.success.cms_contact.delete'), response);
        }, function(response) {
            $log.error('Error while deleteing contact ', response);
            NotificationService.addError(Translator.trans('notification.error.cms_contact.delete'), response);
        });
    };
}]);
