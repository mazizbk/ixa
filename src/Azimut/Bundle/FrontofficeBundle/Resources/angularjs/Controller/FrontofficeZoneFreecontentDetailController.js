/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-11 16:14:10
 */

'use strict';

angular.module('azimutFrontoffice.controller')
.controller('FrontofficeZoneFreecontentDetailController', [
'$scope', '$controller', '$state', '$stateParams', '$log', 'baseStateName',
function ($scope, $controller, $state, $stateParams, $log, baseStateName) {
    // extend CmsFileDetailController
    angular.extend(this, $controller('CmsFileDetailController', {$scope: $scope, baseStateName: baseStateName}));

    $log = $log.getInstance('FrontofficeZoneFreecontentDetailController');

    $scope.showBreadcrumb = false;

    // redefine go back function
    $scope.stateGoBack = function() {
        $state.go('backoffice.frontoffice.page_detail.zones', {pageId: $scope.zone.parentElement.id});
    };
}]);
