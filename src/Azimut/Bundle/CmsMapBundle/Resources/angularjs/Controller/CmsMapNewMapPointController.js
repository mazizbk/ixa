/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:05:33
 */

'use strict';

angular.module('azimutCmsMap.controller')
.controller('CmsMapNewMapPointController', [
'$scope', '$controller', '$state', '$log',
function ($scope, $controller, $state, $log) {
    // extend CmsFileNewController
    angular.extend(this, $controller('CmsNewFileController', {$scope: $scope}));

    $log = $log.getInstance('CmsMapNewMapPointController');

    $scope.stateGoBack = function(map) {
        $state.go('backoffice.cmsmap.map_point_detail', {file_id: map.id});
    };
}]);
