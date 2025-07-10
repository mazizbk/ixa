/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:42:17
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterNewMediaController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'MediacenterFileFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$parse',
function($log, $scope, $rootScope, FormsBag, MediacenterFileFactory, $state, $stateParams, NotificationService, $timeout, $parse) {
    $log = $log.getInstance('MediacenterNewMediaController');

    $scope.$parent.showContentView = true;

    //TODO : check supported types

    var folder = MediacenterFileFactory.findFileFromPath($stateParams.filePath);

    if(!folder) {
        NotificationService.addCriticalError(Translator.trans('notification.error.file.%file_path%.get', { 'file_path' : $stateParams.filePath }));
        $scope.$parent.showContentView = false;
        return;
    }

    if(folder.fileType != 'folder') {
        NotificationService.addError(Translator.trans('notification.error.media.create.inside.another'));
        return;
    }

    $scope.folder = folder;

    $scope.formMediaTemplateUrl = Routing.generate('azimut_mediacenter_backoffice_jsview_media_form_with_one_declination',{ type: $stateParams.mediaType });

    $scope.forms = new FormsBag();

    $scope.addMedia = function(mediaData, filesdata, folder) {
        if(!window.FormData) {
            $log.error("Browser does not support FormData");
            return false;
        }

        // remove dirty state on form
        if (undefined != $scope.forms.params.media.formController) {
            $scope.forms.params.media.formController.$setPristine();
        }

        if(null != filesdata && filesdata.length>0) {
            //TODO: search for a way to automate name building ".mediaDeclinations[0].file"
            mediaData.mediaDeclinations[0].file = filesdata[0];
        }

        mediaData.folder = folder.id;

        return MediacenterFileFactory.createMedia(mediaData).then(function (response) {
            var media = response.media;

            $log.info('Media created', response);

            $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath:media.parentFile.getFullpath()});

            NotificationService.addSuccess(Translator.trans('notification.success.media.create'), response);

            // clear form error messages
            delete $scope.forms.errors.media;

            //TODO : scroll to file
            $scope.selectFile(media);

        }, function(response) {
            $log.error('Media creation failed', response);
            NotificationService.addError(Translator.trans('notification.error.media.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.media = response.data.errors;
            }
        });

    }

    $scope.forms.data.media = {
        folder: folder.id,
        type: $stateParams.mediaType,
        mediaType: {},
        mediaDeclinations: [{
            name: 'Original',
            //type: $stateParams.mediaType,
            mediaDeclinationType: {}
        }]
    };

    $scope.formLocale = $rootScope.locale;

    //for media declination
    //this will contain the files chosen in the file input field (see azFileInput directive)
    $scope.forms.files.media = [];

    $scope.forms.params.media = {
        submitActive: true,
        submitLabel: Translator.trans('create.media'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addMedia($scope.forms.data.media, $scope.forms.files.media, $scope.folder);
        },
        cancelAction: function() {
            $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail', {filePath: folder.getFullpath()});
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('media.has.not.been.saved.are.you.sure.you.want.to.continue')
    };
}]);
