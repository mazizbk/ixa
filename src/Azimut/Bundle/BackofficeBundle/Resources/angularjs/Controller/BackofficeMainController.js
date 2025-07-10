/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-30 10:57:22
 */

'use strict';

angular.module('azimutBackoffice.controller')

.controller('BackofficeMainController', [
'$scope', '$rootScope', '$log', '$state', 'BackofficeMenuFactory', 'BackofficeDiskQuotaFactory',
function($scope, $rootScope, $log, $state, BackofficeMenuFactory, BackofficeDiskQuotaFactory) {

    $log = $log.getInstance('BackofficeMainController');

    $scope.backofficeAppStatus = {
        loading: true
    };

    $scope.setPageTitle = function(title) {
        $rootScope.pageTitle = title;

        $scope.backofficeMenu = BackofficeMenuFactory.getMenu();
    }

    $scope.setPageTitle(Translator.trans('backoffice.app.name'));

    $scope.diskInfos = null;


    $scope.diskInfos = BackofficeDiskQuotaFactory.getInfos();

    $log.info('Active locale: ' + $scope.locale);

    switch($scope.locale) {
        case 'fr':
            $scope.dateFormat = 'dd/MM/yyyy';
            $scope.timeFormat = "H'h'mm";
            break;
        default:
            $scope.dateFormat = 'MM/dd/yyyy';
            $scope.timeFormat = 'h:mma';
            break;
    }

    $scope.datetimeFormat = $scope.dateFormat+' - '+$scope.timeFormat;

    // find wich menu is currently active
    for (var i = $scope.backofficeMenu.length - 1; i >= 0; i--) {
        // for external apps, also check if appName param is the same
        if ('backoffice.external_app' == $state.current.name) {
            if (undefined != $scope.backofficeMenu[i].stateParams && $state.includes($scope.backofficeMenu[i].stateName, {'appName': $scope.backofficeMenu[i].stateParams.appName})) {
                $scope.backofficeMenu[i].active = true;
            }
        }
        else if ($state.includes($scope.backofficeMenu[i].stateName)) {
            $scope.backofficeMenu[i].active = true;
        }
    }

    $scope.openMenuItem = function(menuItem) {
        var promise;

        $scope.backofficeAppStatus.loading = true;

        if('backoffice.external_app' == menuItem.stateName) {
            promise = $state.go(menuItem.stateName, menuItem.stateParams, {reload: undefined != menuItem.stateParams && $state.includes(menuItem.stateName, {'appName': menuItem.stateParams.appName})});
        }
        else if (!$state.includes(menuItem.stateName)) {
            promise = $state.go(menuItem.stateName, menuItem.stateParams);
        }
        else {
            promise = $state.go(menuItem.stateName, menuItem.stateParams, {reload: true});
        }

        promise.then(function() {
            // set all menu items to inactive
            for (var i = $scope.backofficeMenu.length - 1; i >= 0; i--) {
                $scope.backofficeMenu[i].active = false;
            }

            menuItem.active = true;
        });
    }

    $scope.goHome = function() {
        $state.go('backoffice.dashboard');
    };

    $scope.backofficeAppStatus.loading = true;
    $scope.isBackofficeAppLoading = $rootScope.isBackofficeAppLoading;

    $scope.$on('$viewContentLoaded', function(evt) {
        $scope.backofficeAppStatus.loading = false;
    });

    //at startup, go to dashboard
    if ($state.current.name == 'backoffice') $state.go('backoffice.dashboard');
}]);
