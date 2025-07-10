/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:14:11
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityMainController',[
'$log', '$scope', '$rootScope', '$state', 'NotificationService', 'SecurityGroupFactory',
function($log, $scope, $rootScope, $state, NotificationService, SecurityGroupFactory) {
    $log = $log.getInstance('SecurityMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if(!SecurityGroupFactory.isGrantedUser()) {
        $log.warn("User has not access to SecurityGroupFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'security'});
        return;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('security.meta.title'));

    //available locales in application
    if(null == $rootScope.locales) $rootScope.locales = ['en'];

    //current locale in interface
    if(null == $rootScope.locale) $rootScope.locale = 'en';

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    $scope.$on('$stateChangeStart', function(evt) {
        NotificationService.clear();
    });

    //retrieve data from your entity factory like for example:
    //ex: $scope.files = FileFactory.files();

    $scope.showContentView = true;

    $scope.isMainContentLoading = false;

    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    $scope.groups = SecurityGroupFactory.groups();

    $scope.openUserList = function(group) {
        var groupId = null;
        if(undefined != group) groupId = group.id;
        $state.go('backoffice.security.user_list',{groupId: groupId});
    }

    $scope.openGroupList = function() {
        $state.go('backoffice.security.group_list');
    }

    //at startup, go to default substate
    if($state.current.name == 'backoffice.security') $state.go('backoffice.security.user_list');
}]);
