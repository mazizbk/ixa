/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 09:27:24
 */

'use strict';

angular.module('azimutFrontofficeSecurity.controller', []);
angular.module('azimutFrontofficeSecurity.directive', []);
angular.module('azimutFrontofficeSecurity.service', []);
angular.module('azimutFrontofficeSecurity.filter', []);

angular.module('azimutFrontofficeSecurity', [
    'azimutBackoffice',
    'azimutFrontofficeSecurity.controller',
    'azimutFrontofficeSecurity.directive',
    'azimutFrontofficeSecurity.service',
    'azimutFrontofficeSecurity.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider',
function($stateProvider) {
    $stateProvider
        //main state
        .state('backoffice.frontofficesecurity', {
            url: "/frontofficesecurity",
            templateUrl: Routing.generate('azimut_frontofficesecurity_backoffice_jsview_main'),
            resolve: {
                // initialise your entity factories data here
                userFactoryInitPromise: function(FrontofficeSecurityUserFactory) {
                    return FrontofficeSecurityUserFactory.init();
                },
            },
            controller: 'FrontofficeSecurityMainController'
        })

        .state('backoffice.frontofficesecurity.user_list', {
            url: '/users',
            templateUrl: Routing.generate('azimut_frontofficesecurity_backoffice_jsview_user_list'),
            controller: 'FrontofficeSecurityUserListController'
        })

        .state('backoffice.frontofficesecurity.user_detail', {
            url: '/users/user_:id',
            templateUrl: Routing.generate('azimut_frontofficesecurity_backoffice_jsview_user_detail'),
            controller: 'FrontofficeSecurityUserDetailController'
        })

        .state('backoffice.frontofficesecurity.new_user', {
            url: '/new_user',
            templateUrl: Routing.generate('azimut_security_backoffice_jsview_new_user'),
            controller: 'FrontofficeSecurityNewUserController'
        })
    ;
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {
    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('frontoffice_security.app.name'),
        icon: 'glyphicon-pro glyphicon-pro-group-chat',
        stateName: 'backoffice.frontofficesecurity',
        displayOrder: 60
    });
}]);

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutFrontofficeSecurity');
