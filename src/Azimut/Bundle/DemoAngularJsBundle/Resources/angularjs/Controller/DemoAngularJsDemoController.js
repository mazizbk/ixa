/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:16:05
 */

'use strict';

angular.module('azimutDemoAngularJs.controller')

.controller('DemoAngularJsDemoController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService',
function($log, $scope, $state, $stateParams, NotificationService) {
    $log = $log.getInstance('DemoAngularJsDemoController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    // do stuff, call api, retrieve data
    // DemoAngularJsDemoFactory.getFile(...).then(function(response) {
    //     $scope.mainContentLoaded();
    // });

    //retrieve a state param (url param equivalent)
    var myParam = $stateParams.myParam;

    $scope.myVar = "this is a variable passed in state params : "+myParam;
}]);
