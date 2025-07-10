/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 12:14:09
 */

'use strict';

angular.module('azimutModeration.controller')

.controller('ModerationMainController',[
'$log', '$scope','$state', 'NotificationService',
function($log, $scope, $state, NotificationService) {
    $log = $log.getInstance('ModerationMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    // if(!ModerationDemoFactory.isGrantedUser()) {
    //     $log.warn("User has not access to ModerationDemoFactory data");
    //     $state.go('backoffice.forbidden_app', {appName: 'moderation'});
    //     return;
    // }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('moderation.meta.title'));

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
    //ex: $scope.files = FileFactory.files();

    // when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    // at startup, go to default substate
    if($state.current.name == 'backoffice.moderation') $state.go('backoffice.moderation.cms_file_buffer_list');
}]);
