/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:05:33
 */

'use strict';

angular.module('azimutCmsMap.controller')
.controller('CmsMapMapPointDetailController', [
'$scope', '$controller', '$state', '$log', 'baseStateName',
function ($scope, $controller, $state, $log, baseStateName) {
    // extend CmsFileDetailController
    angular.extend(this, $controller('CmsFileDetailController', {$scope: $scope, baseStateName: baseStateName}));

    $log = $log.getInstance('CmsMapMapPointDetailController');

    $scope.showBreadcrumb = false;

    $scope.stateGoBack = function(file_id) {
        if (file_id) {
            $state.go('backoffice.cmsmap.map_point_detail', {file_id: file_id});
        }
        else {
            $state.go('backoffice.cmsmap', {file_id: file_id});
        }
    };
}]);
