/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:43:09
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterNewMediaDeclinationController', [
'$scope', '$rootScope', 'FormsBag', 'MediacenterFileFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$log',
function($scope, $rootScope, FormsBag, MediacenterFileFactory, $state, $stateParams, NotificationService, $timeout, $log) {
    $log = $log.getInstance('MediacenterMainController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    //this will contain the files chosen in the file input field (see azFileInput directive)
    $scope.forms.files.media_declination = [];

    $scope.forms.params.media_declination = {
        submitActive: true,
        submitLabel: Translator.trans('create.declination'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addMediaDeclination($scope.forms.data.media_declination, $scope.forms.files.media_declination, $scope.media);
        },
        cancelAction: function() {
            $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail', {filePath: media.getFullpath()});
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('media.declination.has.not.been.saved.are.you.sure.you.want.to.continue')
    };

    //TODO : check supported types

    MediacenterFileFactory.findFileFromPath($stateParams.filePath).then(function(response) {
        var media = response.data.file;

        if (!media) {
            NotificationService.addCriticalError(Translator.trans('notification.error.file.%file_path%.get', { 'file_path' : $stateParams.filePath }));
            $scope.$parent.showContentView = false;
            return;
        }

        if (media.fileType != 'media') {
            NotificationService.addError(Translator.trans('notification.error.media.declination.%filePath%.is.not.a.media', { 'filePath' : $stateParams.filePath }));
            return;
        }

        $scope.media = media;

        $scope.forms.data.media_declination = {
            name: 'my new '+$stateParams.mediaDeclinationType+' declination',
            media: media.id,
            type: $stateParams.mediaDeclinationType,
            mediaDeclinationType: {}
        };
    });

    $scope.formMediaDeclinationTemplateUrl = Routing.generate('azimut_mediacenter_backoffice_jsview_media_declination_form',{ type: $stateParams.mediaDeclinationType });

    $scope.addMediaDeclination = function(mediaDeclination, filesdata, media) {

        if(!window.FormData) {
            $log.error("Browser does not support FormData");
            return false;
        }

        //if(window.FormData) {

            if(null != filesdata && filesdata.length>0) {
                mediaDeclination.file = filesdata[0];
            }

            mediaDeclination.media = media.id;

            return MediacenterFileFactory.createMediaDeclination(mediaDeclination).then(function (response) {
                // remove dirty state on form
                if (undefined != $scope.forms.params.media_declination.formController) {
                    $scope.forms.params.media_declination.formController.$setPristine();
                }

                if(!$state.is('files.detail')) $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath:media.getFullpath()});
                // clear form error messages
                delete $scope.forms.errors.mediaDeclination;
            }, function(response) {
                $log.error('Failed to create media declination: ', response);
                NotificationService.addError(Translator.trans('notification.error.media.declination.create'), response);

                // display form error messages
                if(undefined != response.data.errors) {
                    $scope.forms.errors.mediaDeclination = response.data.errors;
                }
            });

        /*
        }

        // old browsers fallback
        // WARNING : by doing so, the data are not processed by MediacenterFileFactory service
        // the fields won't be controlled or altered
        else {
            $log.log("fallback mode");
            var form = document.getElementById('form_post_media_declination');

            document.getElementById('media_declination_media').value = media.id;

            var iframe = document.createElement('iframe');
            iframe.setAttribute('src', '');
            iframe.width = 640;
            iframe.height = 480;
            iframe.id = 'iframe_post_media_declination';
            iframe.name = 'iframe_post_media_declination';
            iframe.setAttribute('data-url-callback', media.getFullpath())
            iframe.style.display = 'none';
            //TODO : hide iframe
            //TODO : change content type of response ? (ie ask to user if he want to open or save the file because he don't recognise the mime)

            form.appendChild(iframe);

            //ask for xml format, because ie9 doesn't recognise json
            //form.action = Routing.generate('azimut_mediacenter_api_post_mediadeclinations',{ _format: 'xml' });
            form.action = Routing.generate('azimut_mediacenter_api_post_mediadeclinations');
            form.target = 'iframe_post_media_declination';

            document.getElementById('media_declination_type').value = mediaDeclination.type;
            form.submit();

            //$scope.state = $state;


//            $scope.iframeCallbackGotoState = function(mediaPath) {
//                $log.log("iframeCallbackGotoState : ",mediaPath);
//                $log.log('$state : ',$state);
//                if(!$state.is('files.detail')) $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath:mediaPath});
//            }

            iframe.onload = function() {
                //$log.log('data-url-callback : ',this.getAttribute('data-url-callback'));
                //$log.log($scope);
                $log.log("iframe loaded");
                //$scope.iframeCallbackGotoState(this.getAttribute('data-url-callback'));
                //TODO : read iframe content to handle errors
                //if(!$scope.state.is('files.detail')) $scope.state.go('files.detail',{filePath:this.getAttribute('data-url-callback')});
            }

            //TODO : refresh media thumb if main declination

            $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath:media.getFullpath()});
        }*/
    }
}]);
