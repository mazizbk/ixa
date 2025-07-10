/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 10:38:55
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficePageLayoutsListController', [
'$log', '$scope', 'FrontofficePageLayoutFactory', 'NotificationService', '$state', 'DataSortDefinitionBuilder',
function($log, $scope, FrontofficePageLayoutFactory, NotificationService, $state, DataSortDefinitionBuilder) {
    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.pageLayoutsSortDefinitionBuilder = new DataSortDefinitionBuilder('frontoffice-page-layouts', [
        {
            'label': Translator.trans('name'),
            'property': 'name',
            'default': true
        },
        {
            'label': Translator.trans('creation.date'),
            'property': 'id',
            'reverse': true
        }
    ]);

    FrontofficePageLayoutFactory.getPageLayouts().then(function(pageLayouts) {
        $scope.pageLayouts = pageLayouts;
        $scope.mainContentLoaded();
    });

    $scope.openPageLayout = function(pageLayout) {
        $state.go('backoffice.frontoffice.page_layout_detail', {id: pageLayout.id});
    };

    $scope.deletePageLayout = function(pageLayout) {
        FrontofficePageLayoutFactory.deletePageLayout(pageLayout).then(function(response) {
            $scope.pageLayouts.splice($scope.pageLayouts.indexOf(pageLayout), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.page_layout.delete'));
        }, function(response) {
            $log.error('Delete page layout failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.page_layout.delete'), response);
        });
    }

}]);
