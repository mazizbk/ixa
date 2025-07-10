/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:28:37
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterMainController',[
'$log', '$scope', '$rootScope', 'FormsBag', 'MediacenterFileFactory', 'ArrayExtra', '$state', 'NotificationService',
function($log, $scope, $rootScope, FormsBag, MediacenterFileFactory, ArrayExtra, $state, NotificationService) {
    $log = $log.getInstance('MediacenterMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;
    $scope.Routing = Routing;

    //available locales in application
    if(null == $rootScope.locales) $rootScope.locales = ['en'];

    //current locale in interface
    if(null == $rootScope.locale) $rootScope.locale = 'en';

    var handleWidgetMode = function() {
        // handle widget mode
        if(null != $scope.appScope.widgetId) {
            $log.info("Mediacenter loaded in widget mode");

            if(null == $scope.appScope.azimutWidgetsParams[$scope.appScope.widgetId].params) {
                $log.error('Missing parameter object for Mediacenter widget ($scope.appScope.azimutWidgetsParams['+$scope.appScope.widgetId+'].params)');
                return;
            }

            $scope.widgetMode = true;
            $scope.mediacenterParams = $scope.appScope.azimutWidgetsParams[$scope.appScope.widgetId].params;
        }
        else {
            $scope.setPageTitle(Translator.trans('mediacenter.meta.title'));

            $scope.mediacenterParams = {
                statePrefix: 'backoffice'
            }
        }
    };

    handleWidgetMode();
    $scope.$watch('appScope.widgetId', handleWidgetMode);

    if(!MediacenterFileFactory.isGrantedUser()) {
        $log.warn("User has not access to MediacenterFileFactory data");
        $state.go($scope.mediacenterParams.statePrefix+'.mediacenter_forbidden');
        return;
    }

    $scope.$on('$stateChangeStart', function(evt) {
        NotificationService.clear();
    });

    $scope.isMainContentLoading = false;

    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    $scope.files = MediacenterFileFactory.files();

    $scope.selectedFile = null;
    // currently opened file
    $scope.currentFile = null;

    // when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    // list of possible medias types
    $scope.availableMediaTypes = MediacenterFileFactory.availableMediaTypes();

    $scope.openFile = function(file) {
        if('temp' == file.fileType) {
            $log.warn('Cannot open a temporary file.');
            NotificationService.addWarning(Translator.trans('notification.warning.can.not.open.temp.file'));
            return;
        }

        // in widget mode, if the media has only one declination, we don't open it but select and return it
        if($scope.widgetMode && 'media' == file.fileType && 1 == file.declinations.length) {
            $scope.widgetSelectMedia(file);
            return;
        }

        $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail', {filePath:file.getFullpath()});

        $scope.selectedFile = null;
    }

    $scope.selectFile = function(file) {
        $scope.selectedFile = file;

        $scope.selectedFilePublicationsCount = null;
        if ('media' == file.fileType) {
            MediacenterFileFactory.getFilePublications(file.id).then(function (response) {
                $scope.selectedFilePublicationsCount = response.data.publications_count;
                $scope.selectedFilePublications = response.data.publications;
            });
        }

        if ('folder' == file.fileType) {
            MediacenterFileFactory.refreshFolderSize(file);
        }
    }

    $scope.setCurrentFile = function(file) {
        $scope.currentFile = file;
    }

    $scope.filesWaitingForUpload = MediacenterFileFactory.filesWaitingForUpload();

    $scope.trashBin = {
        fileType: 'trash_bin'
    }

    // handler for media and folder dropped on folder
    $scope.$on('dropEvent', function(evt, dragged, dropped, droppedFiles) {
        $log.log("dropEvent");

        //we have dropped an element dragged into the interface
        if(dragged) {

            // if dropped is not a file, then ignore it
            if(undefined == dropped.fileType) return;

            // this handler is for dropping on folder only
            if(dropped.fileType != 'folder') {
                return;
            }

            //prevent files from being dropped on themselves
            if(dragged == dropped) {
                $log.log("File dropped on itself. Aborting.");
                return;
            }

            //TODO : check if a file or folder has the same name
            //...

            if(!dragged.fileType) {
                $log.error('Dragged file object type unspecified');
                return false;
            }

            if('temp' == dragged.fileType) {
                $log.warn('Cannot move a temporary file. Waiting for server response.');
                NotificationService.addWarning(Translator.trans('notification.warning.can.not.move.temp.file'));
                return false;
            }

            if(dragged.fileType != 'folder' && dragged.fileType != 'media') {
                $log.warn('Dragged file object type "'+dragged.fileType+'" is not supported');
                NotificationService.addWarning(Translator.trans('notification.warning.dropped.file.%type%.not.supported', { 'type' : dragged.fileType }));
                return false;
            }

            // do nothing if the file has been dropped on its actual folder
            if(dragged.parentFile == dropped) {
                $log.log("Dropped file on its actual folder. Do not update.");
                return;
            }

            if(dragged.fileType == 'folder') {
                if(!dragged.parentFile) {
                    $log.warn("Cannot move a root folder.");
                    return;
                }

                // move file on the interface before calling server
                dragged.parentFile.subfolders.splice(dragged.parentFile.subfolders.indexOf(dragged), 1);
                dropped.subfolders.push(dragged);
                dragged.oldParentFile = dragged.parentFile;
                dragged.parentFile = dropped;
                dropped.showSubfolders = true;

                var folderMoveData = {
                    id: dragged.id,
                    parentFolder: dropped.id
                }

                MediacenterFileFactory.updateFolder(folderMoveData, 'patch').then(function(response) {
                    NotificationService.addSuccess(Translator.trans('notification.success.folder.move'));
                }, function(response) {
                    $log.error('Update folder after drop failed', response);
                    NotificationService.addError(Translator.trans('notification.error.folder.move'), response);

                    // revert file moving on interface
                    dragged.parentFile = dragged.oldParentFile;
                    dropped.subfolders.splice(dropped.subfolders.indexOf(dragged), 1);
                    dragged.parentFile.subfolders.push(dragged);


                });
            }
            else if(dragged.fileType == 'media') {
                // move the file on interface for quick feedback (may be revert in MediacenterFileFactory if error occurs)
                dragged.parentFile.medias.splice(dragged.parentFile.medias.indexOf(dragged), 1);
                dropped.medias.push(dragged);
                dragged.oldParentFile = dragged.parentFile;
                dragged.parentFile = dropped;

                var mediaMoveData = {
                    id: dragged.id,
                    folder: dropped.id
                }

                MediacenterFileFactory.updateMedia(mediaMoveData, 'patch').then(function(response) {
                    NotificationService.addSuccess(Translator.trans('notification.success.media.move'));
                }, function(response) {
                    $log.error('Update media after drop failed', response);
                    NotificationService.addError(Translator.trans('notification.error.media.move'), response);

                    // revert file moving on interface
                    dragged.parentFile = dragged.oldParentFile;
                    dropped.medias.splice(dropped.medias.indexOf(dragged), 1);
                    dragged.parentFile.medias.push(dragged);

                });
            }
        }

        // we have dropped a file dragged outside the interface (drop from desktop)
        else if(droppedFiles && droppedFiles.length>0) {
            // if dragged is not specified, then it is maybe a drop from the desktop
            angular.forEach(droppedFiles, function(droppedFile, droppedFileKey){

                //if(droppedFile.type != '') { // changed this because some file don't have a type property (.odp in webkit for example)
                if(0 != droppedFile.size) {

                    MediacenterFileFactory.createMediaFromDroppedFile(droppedFile,dropped.id).then(function (response) {

                    }, function(response) {
                        $log.error('Create media after drop failed', response);
                        NotificationService.addError(
                            droppedFile.name+' : '+Translator.trans('your.file.could.not.be.saved'),
                            response,
                            {
                                display_form_errors: true,
                                message_if_no_server_data: Translator.trans('maybe.your.file.is.too.large')
                            }
                        );
                    });
                }
                else {
                    $log.warn("Dropped file has no type, it is probably a folder. Ignoring it.");
                    //TODO : handle folder drop, or error
                }
            });

        }

        else {
            $log.error("Could not handle dropped element");
            NotificationService.addError(Translator.trans('notification.error.dropped.not.supported'));
        }

        $rootScope.draggedElement = null;

        //propagate promise resolution, update scope
        $scope.$apply();
    });

    // handler for media and folder dropped on trash bin
    $scope.$on('dropEvent', function(evt, dragged, dropped, droppedFiles) {
        //we have dropped an element dragged into the interface
        if(!dragged) return;

        // if dropped is not a file, then ignore it
        if(undefined == dropped.fileType) return;

        // this handler is for dropping on trash bin only
        if(dropped.fileType != 'trash_bin') {
            return;
        }

        if(dragged.fileType == 'folder' || dragged.fileType == 'media') {
            $scope.trashFile(dragged);
        }
        else {
            $log.error("Could not handle dropped element on trash bin");
        }

    });

    //shared function for children controllers

    $scope.openTrashBin = function() {
        $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.trash_bin');
    }

    $scope.trashFile = function(file) {
        if(file.fileType == 'folder') {
            MediacenterFileFactory.trashFolder(file).then(function (response) {
                if($scope.currentFile === file) {
                    $scope.openFile(file.parentFile);
                }
                NotificationService.addSuccess(Translator.trans('notification.success.folder.trash'), response);
            }, function(response) {
                $log.error('Error while trashing folder', response);
                NotificationService.addError(Translator.trans('notification.error.folder.trash'), response);
            });
        }
        else if(file.fileType == 'media') {
            MediacenterFileFactory.trashMedia(file).then(function (response) {
                NotificationService.addSuccess(Translator.trans('notification.success.media.trash'), response);

                MediacenterFileFactory.getFilePublications(file.id).then(function (response) {
                    if (response.data.publications_count > 0) {
                        NotificationService.addWarning(Translator.trans('notification.warning.you.trashed.a.media.linked.to.%count%.elements', {'count': response.data.publications_count}), response);
                    }
                });

            }, function(response) {
                $log.error('Error while trashing media', response);
                NotificationService.addError(Translator.trans('notification.error.media.trash'), response);
            });
        }
        else {
            $log.error('File object type "'+file.fileType+'" is not supported');
        }
    }

    $scope.widgetCallback = function(mediaDeclinations) {
        var options = null;

        $scope.appScope.azimutWidgetsParams[$scope.widgetId].callbacks['azimutMediacenterChooseMediaDeclinations'](mediaDeclinations,options);
    }

    $scope.widgetSelectMedia = function(media) {
        $scope.widgetCallback([{
            id: media.mainDeclination.id,
            name: media.name,
            path: media.path,
            thumb: ('image' == media.mediaType)? media.path : null,
            cssIcon: media.cssIcon,
            mediaType: media.mediaType
        }]);
    }

    $scope.widgetSelectMediaDeclination = function(mediaDeclination) {
        var media = MediacenterFileFactory.findMedia(mediaDeclination.media);

        $scope.widgetCallback([{
            id: mediaDeclination.id,
            name: media.name +' ('+ mediaDeclination.name +')',
            path: mediaDeclination.mediaDeclinationType.path,
            cssIcon: media.cssIcon,
            mediaType: media.mediaType
        }]);
    }


    //at startup, go to first root folder state
    if($state.current.name == $scope.mediacenterParams.statePrefix+'.mediacenter' && undefined != $scope.files[0]) $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath: $scope.files[0].getFullpath()});
}]);
