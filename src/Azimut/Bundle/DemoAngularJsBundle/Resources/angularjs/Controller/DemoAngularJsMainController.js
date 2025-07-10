/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:14:11
 */

'use strict';

angular.module('azimutDemoAngularJs.controller')

.controller('DemoAngularJsMainController',[
'$log', '$scope','$state', 'NotificationService', 'DemoAngularJsDemoFactory',
function($log, $scope, $state, NotificationService, DemoAngularJsDemoFactory) {
    $log = $log.getInstance('DemoAngularJsMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if(!DemoAngularJsDemoFactory.isGrantedUser()) {
        $log.warn("User has not access to DemoAngularJsDemoFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'demo_angular_js'});
        return;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('demo_angular_js.meta.title'));

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
    if($state.current.name == 'backoffice.demoangularjs') $state.go('backoffice.demoangularjs.demo_home_substate');
}]);
