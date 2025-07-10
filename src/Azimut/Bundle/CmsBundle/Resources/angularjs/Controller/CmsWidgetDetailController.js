/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-01 16:16:03
 */

'use strict';

angular.module('azimutFrontoffice.controller')
.controller('CmsWidgetDetailController', [
'$scope', '$controller', '$state', '$log', 'CmsFileFactory', 'baseStateName',
function ($scope, $controller, $state, $log, CmsFileFactory, baseStateName) {
    // extend CmsFileDetailController
    angular.extend(this, $controller('CmsFileDetailController', {$scope: $scope, baseStateName: baseStateName}));

    $log = $log.getInstance('CmsWidgetDetailController');

    $scope.showBreadcrumb = false;

    $scope.stateGoBack = function() {
        $('#azimutCmsEditWidget').hide();
        $state.go(baseStateName, {}, {reload: true});
    };
}]);
