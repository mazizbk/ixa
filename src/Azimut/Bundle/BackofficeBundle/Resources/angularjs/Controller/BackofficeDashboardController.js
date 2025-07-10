/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-30 10:36:40
 */

'use strict';

angular.module('azimutBackoffice.controller')

.controller('BackofficeDashboardController', [
'$log', '$scope', '$templateCache',
function($log, $scope, $templateCache) {

    $log = $log.getInstance('BackofficeDashboardController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('dashboard.meta.title'));

    $scope.isMainContentLoading = false;
    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }


    // exclude template from cache
    $templateCache.remove(Routing.generate('azimut_backoffice_backoffice_jsview_dashboard'));

}]);
