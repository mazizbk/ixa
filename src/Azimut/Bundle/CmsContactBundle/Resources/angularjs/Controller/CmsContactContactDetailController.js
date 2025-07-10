/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:16:05
 */

'use strict';

angular.module('azimutCmsContact.controller')
.controller('CmsContactContactDetailController', [
'$scope', '$state', '$stateParams', '$log', 'CmsFileFactory', 'NotificationService',
function ($scope, $state, $stateParams, $log, CmsFileFactory, NotificationService) {
    $log = $log.getInstance('CmsContactContactDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    CmsFileFactory.getFile($stateParams.file_id).then(function(response) {
        $scope.contact = response.data.cmsFile;
        $scope.mainContentLoaded();
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.contact.%id%.get', { 'id' : $stateParams.file_id }));
        $scope.$parent.showContentView = false;
        return;
    });
}]);
