/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:38:14
 */

'use strict';

angular.module('azimutBackoffice.controller', []);
angular.module('azimutBackoffice.directive', []);
angular.module('azimutBackoffice.service', []);
angular.module('azimutBackoffice.filter', []);

angular.module('azimutBackoffice', [
    'azimutBackoffice.controller',
    'azimutBackoffice.directive',
    'azimutBackoffice.service',
    'azimutBackoffice.filter',
    'ui.router',
    'ui.event',
    'ngAnimate',
    'ui.bootstrap'
])

.config([
'$stateProvider', '$urlRouterProvider', '$httpProvider', '$urlMatcherFactoryProvider', 'MediacenterStateProvider',
function($stateProvider, $urlRouterProvider, $httpProvider, $urlMatcherFactoryProvider, MediacenterStateProvider) {

    // special url type for blocking URI encode inside UI Router (ex: for slashes)
    // example use : url: '/{filePath:nonURIEncoded}' instead of url: '/*filePath:nonURIEncoded' in previous UI Router versions
    $urlMatcherFactoryProvider.type('nonURIEncoded', {
        encode: function (val) {
            return val !== null ? val.toString() : val;
        },
        decode: function (val) {
            return val !== null ? val.toString() : val;
        },
        is: function () { return true; }
    });


    // For any unmatched url
    $urlRouterProvider.otherwise('/home');

    $stateProvider

        .state('backoffice', {
            url: "/home",
            templateUrl: Routing.generate('azimut_backoffice_backoffice_jsview_main'),
            controller: 'BackofficeMainController'
        })

        //nested view (url is relative to the parent state !)
        .state('backoffice.dashboard', {
            url: '/dashboard',
            templateUrl: Routing.generate('azimut_backoffice_backoffice_jsview_dashboard'),
            controller: 'BackofficeDashboardController'
        })

        .state('backoffice.debug', {
            url: '/debug',
            templateUrl: Routing.generate('azimut_backoffice_backoffice_jsview_debug'),
            controller: 'BackofficeDebugController'
        })

        .state('backoffice.forbidden_app', {
            url: '/forbidden_:appName',
            templateUrl: Routing.generate('azimut_backoffice_backoffice_jsview_forbiddden_application'),
            controller: 'BackofficeForbiddenApplicationController'
        })

        .state('backoffice.external_app', {
            url: '/external_:appName',
            templateUrl: Routing.generate('azimut_backoffice_jsview_external_app'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.external_app';
                }
            },
            controller: 'BackofficeExternalAppController'
        })
    ;

    MediacenterStateProvider.attachStatesTo('backoffice.external_app');

    // attach an interceptor for all HTTP requests
    $httpProvider.interceptors.push('HttpRequestInterceptor');

    // intercept included template HTTP error
    $httpProvider.interceptors.push('HttpRequestTemplateInterceptor');

}])

//this function is called before controllers
.run([
'$rootScope', 'BackofficeMenuFactory', 'NaturalSorting', '$log',
function($rootScope, BackofficeMenuFactory, NaturalSorting, $log) {

    $log = $log.getInstance('BackofficeApp');

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('dashboard.app.name'),
        icon: 'glyphicon-dashboard',
        stateName: 'backoffice.dashboard',
        displayOrder: 1
    });

    /*
    // Bug reporter (sends mail)
    BackofficeMenuFactory.addMenuItem({
        title: 'Report bug',
        icon: 'glyphicon glyphicon-pro glyphicon-pro-bug',
        stateName: 'backoffice.debug',
        displayOrder: 99
    });
    */

    // expose shortcut to natural sorting service on the root scope
    $rootScope.natural = function (field) {
        return function (item) {
            return NaturalSorting.naturalValue(item[field]);
        };
    };

    //Tells angularjs to use www form urlencoded by default on POST commands
    //HttpConfigurer.setWwwFormUrlencodedContentType();

}]);
