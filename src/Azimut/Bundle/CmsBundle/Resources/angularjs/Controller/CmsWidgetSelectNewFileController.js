/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-03-01 16:40:44
 */

'use strict';

angular.module('azimutCms.controller')
.controller('CmsWidgetSelectNewFileController', [
'$log','$scope', '$state', '$stateParams', '$controller',
function($log, $scope, $state, $stateParams, $controller) {
    var statePrefix = $state.$current.parent.self.name;

    $scope.isMainContentLoading = false;

    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    };

    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    };

    // extend CmsNewFileController
    angular.extend(this, $controller('CmsNewFileController', {$scope: $scope}));

    $scope.showBreadcrumb = false;

    $log = $log.getInstance('CmsWidgetSelectNewFileController');

    var parentState = $state.$current.parent;
    var widgetId = $scope.appScope.widgetId;

    $scope.stateGoBack = function(file) {
        // Restore widgetId value (avoid collision with the Mediacenter widget)
        $scope.appScope.widgetId = widgetId;

        $state.go(statePrefix + '.widget_select_file', {cmsFileType: null, preselectId: file.id});
    };
}]);
