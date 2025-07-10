/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 09:27:24
 */

'use strict';

angular.module('azimutFrontofficeSecurity.controller')

.controller('FrontofficeSecurityMainController',[
'$log', '$scope','$state', 'NotificationService', 'FrontofficeSecurityUserFactory',
function($log, $scope, $state, NotificationService, FrontofficeSecurityUserFactory) {
    $log = $log.getInstance('FrontofficeSecurityMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if(!FrontofficeSecurityUserFactory.isGrantedUser()) {
        $log.warn("User has not access to FrontofficeSecurityUserFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'frontoffice_security'});
        return;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('frontoffice_security.meta.title'));

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    // clear notification at each state change
    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    $scope.isMainContentLoading = false;

    // set function to allow easy update of loading state in children scopes
    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    $scope.showContentView = true;

    // at startup, go to default substate
    if($state.current.name == 'backoffice.frontofficesecurity') $state.go('backoffice.frontofficesecurity.user_list');
}]);
