/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-18 15:17:52
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterTrashBinController', [
'$log', '$scope', 'MediacenterFileFactory', 'NotificationService',
function($log, $scope, MediacenterFileFactory, NotificationService) {
    $log = $log.getInstance('MediacenterTrashBinController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    MediacenterFileFactory.getTrashedFiles().then(function(response) {
        $scope.medias = response.data.medias;
        $scope.folders = response.data.folders;

        $scope.mainContentLoaded();
    });

    $scope.untrashFile = function(file) {
        if(file.fileType == 'folder') {
            MediacenterFileFactory.untrashFolder(file).then(function (response) {
                $scope.folders.splice($scope.folders.indexOf(file), 1);
                NotificationService.addSuccess(Translator.trans('notification.success.folder.untrash'), response);
            }, function(response) {
                $log.error('Error while untrashing folder ', response);
                NotificationService.addError(Translator.trans('notification.error.folder.untrash'), response);
            });
        }
        else if(file.fileType == 'media') {
            MediacenterFileFactory.untrashMedia(file).then(function (response) {
                $scope.medias.splice($scope.folders.indexOf(file), 1);
                NotificationService.addSuccess(Translator.trans('notification.success.media.untrash'), response);
            }, function(response) {
                $log.error('Error while untrashing media ', response);
                NotificationService.addError(Translator.trans('notification.error.media.untrash'), response);
            });
        }
        else {
            $log.error('File object type "'+file.fileType+'" is not supported');
        }
    }

    $scope.deleteFile = function(file) {
        if(file.fileType == 'folder') {
            MediacenterFileFactory.deleteFolder(file).then(function (response) {
                $scope.folders.splice($scope.folders.indexOf(file), 1);
                NotificationService.addSuccess(Translator.trans('notification.success.folder.delete'), response);

            }, function(response) {
                $log.error('Error while deleting folder ', response);
                NotificationService.addError(Translator.trans('notification.error.folder.delete'), response);
            });
        }
        else if(file.fileType == 'media') {
            MediacenterFileFactory.deleteMedia(file).then(function (response) {
                $scope.medias.splice($scope.folders.indexOf(file), 1);
                NotificationService.addSuccess(Translator.trans('notification.success.media.delete'), response);
            }, function(response) {
                $log.error('Error while deleting media ', response);
                NotificationService.addError(Translator.trans('notification.error.media.delete'), response);
            });
        }
        else {
            $log.error('File object type "'+file.fileType+'" is not supported');
        }
    }

    $scope.toggleOrderFilesBy = function(property) {
        //inverte order if new order is equal to old order
        if($scope.orderFilesBy == property) $scope.orderFilesReverse = !$scope.orderFilesReverse;
        $scope.setOrderFilesBy(property, $scope.orderFilesReverse);
    }

    $scope.setOrderFilesBy = function(property, reverse) {
        $scope.orderFilesBy = property;
        $scope.orderFilesReverse = reverse;

        localStorage.setItem('azimutMediacenter-order-files-by-trashbin', property);
        localStorage.setItem('azimutMediacenter-order-files-reverse-trashbin', reverse);
    }

    $scope.emptyTrashBin = function() {
        MediacenterFileFactory.deleteTrashedFiles().then(function (response) {
            $scope.medias = [];
            $scope.folders = [];
            NotificationService.addSuccess(Translator.trans('notification.success.trashbin.delete'), response);

        }, function(response) {
            $log.error('Error while emptying trash bin', response);
            NotificationService.addError(Translator.trans('notification.error.trashbin.delete'), response);
        });
    }

    // restore file order
    var storedOrderFilesBy = localStorage.getItem('azimutMediacenter-order-files-by-trashbin');
    if(storedOrderFilesBy) {
        var storedOrderFilesReverse = localStorage.getItem('azimutMediacenter-order-files-reverse-trashbin')?true:false;
        $scope.setOrderFilesBy(storedOrderFilesBy, storedOrderFilesReverse);
    }
    // by default, display files ordered by name asc
    else {
        $scope.setOrderFilesBy('name', false);
    }

}]);
