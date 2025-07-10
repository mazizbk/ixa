/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-09 14:25:29
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsTrashBinController', [
'$log', '$scope', 'CmsFileFactory', 'NotificationService',
function($log, $scope, CmsFileFactory, NotificationService) {
    $log = $log.getInstance('CmsTrashBinController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    CmsFileFactory.getTrashedFiles().then(function(response) {
        $scope.cmsFiles = response.data.cmsFiles;

        $scope.mainContentLoaded();
    });

    $scope.untrashFile = function(file) {

        CmsFileFactory.untrashFile(file).then(function (response) {
            $scope.cmsFiles.splice($scope.cmsFiles.indexOf(file), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.cms_file.untrash'), response);

        }, function(response) {
            $log.error('Error while untrashing folder ', response);
            NotificationService.addError(Translator.trans('notification.error.cms_file.untrash'), response);
        });

    }

    $scope.deleteFile = function(file) {

        CmsFileFactory.deleteFile(file).then(function (response) {
            $scope.cmsFiles.splice($scope.cmsFiles.indexOf(file), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.file.delete'), response);

        }, function(response) {
            $log.error('Error while deleting folder ', response);
            NotificationService.addError(Translator.trans('notification.error.file.delete'), response);
        });

    }

    $scope.toggleOrderFilesBy = function(property) {
        //inverte order if new order is equal to old order
        if($scope.orderFilesBy == property) $scope.orderFilesReverse = !$scope.orderFilesReverse;
        $scope.setOrderFilesBy(property, $scope.orderFilesReverse);
    }

    $scope.setOrderFilesBy = function(property, reverse) {
        $scope.orderFilesBy = property;
        $scope.orderFilesReverse = reverse;

        localStorage.setItem('azimutCms-order-files-by-trashbin', property);
        localStorage.setItem('azimutCms-order-files-reverse-trashbin', reverse);
    }

    $scope.emptyTrashBin = function() {
        CmsFileFactory.deleteTrashedFiles().then(function (response) {
            $scope.cmsFiles = [];
            NotificationService.addSuccess(Translator.trans('notification.success.trashbin.delete'), response);

        }, function(response) {
            $log.error('Error while emptying trash bin', response);
            NotificationService.addError(Translator.trans('notification.error.trashbin.delete'), response);
        });
    }

    // restore file order
    var storedOrderFilesBy = localStorage.getItem('azimutCms-order-files-by-trashbin');
    if(storedOrderFilesBy) {
        var storedOrderFilesReverse = localStorage.getItem('azimutCms-order-files-reverse-trashbin')?true:false;
        $scope.setOrderFilesBy(storedOrderFilesBy, storedOrderFilesReverse);
    }
    // by default, display files ordered by name asc
    else {
        $scope.setOrderFilesBy('name', false);
    }
}]);
