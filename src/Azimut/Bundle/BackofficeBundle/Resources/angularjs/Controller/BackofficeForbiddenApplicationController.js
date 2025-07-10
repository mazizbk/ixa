/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-28 10:01:20
 */

 'use strict';

 angular.module('azimutBackoffice.controller')

 .controller('BackofficeForbiddenApplicationController', [
 '$log', '$scope', '$stateParams',
 function($log, $scope, $stateParams) {

    $scope.appName = Translator.trans($stateParams.appName+'.app.name');

 }]);
