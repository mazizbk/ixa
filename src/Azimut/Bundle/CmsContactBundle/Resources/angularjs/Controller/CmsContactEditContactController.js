/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-11-03 16:53:40
 */

'use strict';

angular.module('azimutCmsContact.controller')
.controller('CmsContactContactEditController', [
'$scope', '$controller', '$state', '$log', 'baseStateName',
function ($scope, $controller, $state, $log, baseStateName) {
    // extend CmsFileDetailController
    angular.extend(this, $controller('CmsFileDetailController', {$scope: $scope, baseStateName: baseStateName}));

    $log = $log.getInstance('CmsContactContactEditController');

    $scope.showBreadcrumb = false;

    $scope.stateGoBack = function(file_id) {
        if (file_id) {
            $state.go('backoffice.cmscontact.contact_detail', {file_id: file_id});
        }
        else {
            $state.go('backoffice.cmscontact', {file_id: file_id});
        }
    };
}]);
