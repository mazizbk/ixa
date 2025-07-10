/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2014-01-10 16:39:05
 */

'use strict';

//we declare submodules to be sure all is instanciated when calling app config function
angular.module('azimutDemoAngularJs.controller', []);
angular.module('azimutDemoAngularJs.directive', []);
angular.module('azimutDemoAngularJs.service', []);
angular.module('azimutDemoAngularJs.filter', []);

angular.module('azimutDemoAngularJs', [
    'azimutBackoffice',
    'azimutDemoAngularJs.controller',
    'azimutDemoAngularJs.directive',
    'azimutDemoAngularJs.service',
    'azimutDemoAngularJs.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider',
function($stateProvider) {

    $stateProvider

        //main state
        .state('backoffice.demoangularjs', {
            url: "/demoangularjs",
            templateUrl: Routing.generate('azimut_demoangularjs_backoffice_jsview_main'),
            resolve: {
                // initialise your entity factories data here
                demoFactoryInitPromise: function(DemoAngularJsDemoFactory){
                    return DemoAngularJsDemoFactory.init();
                }
            },
            controller: 'DemoAngularJsMainController'
        })

        //nested state (url is relative to the main state)
        .state('backoffice.demoangularjs.demo_home_substate', {
            url: '/demo_homesubstate',
            templateUrl: Routing.generate('azimut_demoangularjs_backoffice_jsview_demo_home'),
            controller: 'DemoAngularJsDemoHomeController'
        })

        .state('backoffice.demoangularjs.demo_substate', {
            url: '/demo_substate_:myParam',
            templateUrl: Routing.generate('azimut_demoangularjs_backoffice_jsview_demo'),
            controller: 'DemoAngularJsDemoController'
        })
    ;
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('demo_angular_js.app.name'),
        icon: 'glyphicon-info-sign',
        stateName: 'backoffice.demoangularjs',
        displayOrder: 9999
    });

}])

;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutDemoAngularJs');
