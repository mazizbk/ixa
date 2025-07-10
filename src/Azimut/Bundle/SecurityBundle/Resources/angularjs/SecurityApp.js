/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2014-01-10 16:39:05
 */

'use strict';

//we declare submodules to be sure all is instanciated when calling app config function
angular.module('azimutSecurity.controller', []);
angular.module('azimutSecurity.directive', []);
angular.module('azimutSecurity.service', []);
angular.module('azimutSecurity.filter', []);

angular.module('azimutSecurity', [
    'azimutBackoffice',
    'azimutSecurity.controller',
    'azimutSecurity.directive',
    'azimutSecurity.service',
    'azimutSecurity.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider',
function($stateProvider) {

    $stateProvider

        //main state
        .state('backoffice.security', {
            url: "/security",
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_main'),
            resolve: {
                // initialise user factory data here
                userFactoryInitPromise: function(SecurityUserFactory) {
                    return SecurityUserFactory.init();
                },
                groupFactoryInitPromise: function(SecurityGroupFactory) {
                    return SecurityGroupFactory.init();
                },
                accessRightFactoryInitPromise: function(SecurityAccessRightFactory) {
                    return SecurityAccessRightFactory.init();
                }
            },
            controller: 'SecurityMainController'
        })

        //nested state (url is relative to the main state)
        .state('backoffice.security.user_list', {
            url: '/users_:groupId',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_user_list'),
            controller: 'SecurityUserListController'
        })

        .state('backoffice.security.user_detail', {
            url: '/users/user_:id',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_user_detail'),
            controller: 'SecurityUserDetailController'
        })

        .state('backoffice.security.new_user', {
            url: '/new_user',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_new_user'),
            controller: 'SecurityNewUserController'
        })

        .state('backoffice.security.group_list', {
            url: '/groups',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_group_list'),
            controller: 'SecurityGroupListController'
        })

        .state('backoffice.security.group_detail', {
            url: '/groups/group_:id',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_group_detail'),
            controller: 'SecurityGroupDetailController'
        })

        .state('backoffice.security.new_group', {
            url: '/new_group',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_new_group'),
            controller: 'SecurityNewGroupController'
        })
    ;
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('security.app.name'),
        icon: 'glyphicon-pro glyphicon-pro-group',
        stateName: 'backoffice.security',
        displayOrder: 50
    });

}])

;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutSecurity');
