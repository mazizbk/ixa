/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:33:18
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeSitesListController', [
'$log', '$scope', 'FrontofficeSiteFactory', 'NotificationService', 'DataSortDefinitionBuilder',
function($log, $scope, FrontofficeSiteFactory, NotificationService, DataSortDefinitionBuilder) {
    $log = $log.getInstance('FrontofficeSitesListController');

    $scope.$parent.showContentView = true;

    $scope.sitesSortDefinitionBuilder = new DataSortDefinitionBuilder('frontoffice-sites', [
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

    //$scope.sites = FrontofficeSiteFactory.sites();

    $scope.deleteSite = function(site) {
        FrontofficeSiteFactory.deleteSite(site).then(function(response) {
            NotificationService.addSuccess(Translator.trans('notification.success.site.delete'));
        }, function(response) {
            $log.error('Delete site failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.site.delete'), response);
        });
    }
}]);
