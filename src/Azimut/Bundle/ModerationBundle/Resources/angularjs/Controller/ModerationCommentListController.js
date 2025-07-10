/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-19 15:29:07
 */

'use strict';

angular.module('azimutModeration.controller')
.controller('ModerationCommentListController', [
'$scope', '$controller', '$state', '$log', 'baseStateName',
function ($scope, $controller, $state, $log, baseStateName) {
    // extend CmsCommentListController
    angular.extend(this, $controller('CmsCommentListController', {$scope: $scope, baseStateName: baseStateName}));
    $log = $log.getInstance('ModerationCommentListController');

    $scope.visibleFilter = false;
}]);
