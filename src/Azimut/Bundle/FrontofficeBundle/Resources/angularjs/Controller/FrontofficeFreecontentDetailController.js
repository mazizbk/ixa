/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-12-30 14:38:10
 */

'use strict';

angular.module('azimutFrontoffice.controller')
.controller('FrontofficeFreecontentDetailController', [
'$scope', '$controller', '$state', '$log', 'FrontofficePage', 'baseStateName',
function ($scope, $controller, $state, $log, FrontofficePage, baseStateName) {
    // extend CmsFileDetailController
    angular.extend(this, $controller('CmsFileDetailController', {$scope: $scope, baseStateName: baseStateName}));

    $log = $log.getInstance('FrontofficeFreecontentDetailController');

    $scope.showBreadcrumb = false;

    // go back to parent (here menu because page is monozone and zone has only one cmsfile)
    $scope.stateGoBack = function() {
        if ($scope.page.parentElement instanceof FrontofficePage) {
            $scope.openPage($scope.page.parentElement);
        }
        else {
            $scope.openMenu($scope.page.parentElement);
        }
    };
}]);
