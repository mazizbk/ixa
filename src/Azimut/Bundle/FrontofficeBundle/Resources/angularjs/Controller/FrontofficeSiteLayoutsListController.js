/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 14:21:12
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeSiteLayoutsListController', [
'$log', '$scope', 'FrontofficeSiteLayoutFactory', 'NotificationService', '$state', 'DataSortDefinitionBuilder',
function($log, $scope, FrontofficeSiteLayoutFactory, NotificationService, $state, DataSortDefinitionBuilder) {
    $log = $log.getInstance('FrontofficeSiteLayoutsListController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.siteLayoutsSortDefinitionBuilder = new DataSortDefinitionBuilder('frontoffice-site-layouts', [
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

    FrontofficeSiteLayoutFactory.getSiteLayouts().then(function(siteLayouts) {
        $scope.siteLayouts = siteLayouts;
        $scope.mainContentLoaded();
    });

    $scope.openSiteLayout = function(siteLayout) {
        $state.go('backoffice.frontoffice.site_layout_detail', {id: siteLayout.id});
    };

    $scope.deleteSiteLayout = function(siteLayout) {
        FrontofficeSiteLayoutFactory.deleteSiteLayout(siteLayout).then(function(response) {
            $scope.siteLayouts.splice($scope.siteLayouts.indexOf(siteLayout), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.site_layout.delete'));
        }, function(response) {
            $log.error('Delete site layout failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.site_layout.delete'), response);
        });
    }

}]);
