/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:43:36
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterEditDeclinationController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'MediacenterFileFactory', '$state', '$stateParams', 'NotificationService', '$timeout',
function($log, $scope, $rootScope, FormsBag, MediacenterFileFactory, $state, $stateParams, NotificationService, $timeout) {
    $log = $log.getInstance('MediacenterEditDeclinationController');

    $scope.forms = new FormsBag();

    //this will contain the files chosen in the file input field (see azFileInput directive)
    $scope.forms.files.media_declination = [];

    $scope.init = function(mediaDeclinationFormData) {
        $scope.forms.data.media_declination = mediaDeclinationFormData;
    }

    $scope.forms.params.media_declination = {
        submitActive: true,
        submitLabel: Translator.trans('update'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            $scope.forms.data.media_declination.media = $scope.currentFile.id;

            if(!window.FormData) {
                $log.error("Browser do not support FormData. Files won't be sent to server");
                return false;
            }

            return MediacenterFileFactory.updateMediaDeclination($scope.forms.data.media_declination).then(function (response) {
                if (response.data.mediaDeclination.isMainDeclination) {
                    $scope.setMainDeclination($scope.forms.data.media_declination);
                }

                // remove dirty state on form
                if (undefined != $scope.forms.params.media_declination.formController) {
                    $scope.forms.params.media_declination.formController.$setPristine();
                }

                //remove file name from upload queue
                //$scope.filesWaitingForUpload.splice($scope.filesWaitingForUpload.indexOf(fileWaitingForUpload), 1);

                //if(!$state.is('files.detail')) $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath:media.getFullpath()});

                $log.info('Media declination updated', response);
                NotificationService.addSuccess(Translator.trans('notification.success.media.declination.update'), response);

                // clear form error messages
                delete $scope.forms.errors.media_declination;

            }, function(response) {

                $log.error('Failed to update media declination', response);
                NotificationService.addError(Translator.trans('notification.error.media.declination.update'), response);

                // display form error messages
                if(undefined != response.data.errors) {
                    $scope.forms.errors.media_declination = response.data.errors;
                }

                //fileWaitingForUpload.status = 'error';
            });
        },
        cancelAction: function() {
            $state.reload();
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('media.declination.has.not.been.saved.are.you.sure.you.want.to.continue')
    };
}]);
