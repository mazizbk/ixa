/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:41:14
 */

'use strict';

angular.module('azimutMediacenter.controller')

.controller('MediacenterFileDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'MediacenterFileFactory', 'ArrayExtra', '$state', '$stateParams', 'NotificationService', '$timeout', 'MediacenterFile', '$filter', '$q', 'DataSortDefinitionBuilder',
function($log, $scope, $rootScope, FormsBag, MediacenterFileFactory, ArrayExtra, $state, $stateParams, NotificationService, $timeout, MediacenterFile, $filter, $q, DataSortDefinitionBuilder) {
    $log = $log.getInstance('MediacenterFileDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.breadcrumb = {
        elements: []
    };

    MediacenterFileFactory.findFileFromPath($stateParams.filePath).then(function(response) {
        var currentFile = response.data.file;
        var breadcrumbCurrentFile = currentFile;

        do {
            $scope.breadcrumb.elements.unshift(breadcrumbCurrentFile);
        } while (breadcrumbCurrentFile = breadcrumbCurrentFile.parentFile);

        $scope.nbFilesInCurrentFolder = null;

        if (currentFile.fileType == 'folder') {
            MediacenterFileFactory.setLastOpenedFolder(currentFile);

            $scope.nbFilesInCurrentFolder = currentFile.subfolders.length + currentFile.medias.length;

            $scope.filesSortDefinitionBuilder = new DataSortDefinitionBuilder('mediacenter-files-' + currentFile.id, [
                {
                    'label': Translator.trans('name'),
                    'property': 'name',
                    'default': true
                },
                {
                    'label': Translator.trans('creation.date'),
                    'property': 'id',
                    'reverse': true
                },
                {
                    'label': Translator.trans('type'),
                    'property': 'mediaType',
                }
            ]);

            $scope.setListView = function() {
                $scope.setMediaTemplateView('table');
            };

            $scope.setThumbailView = function(width) {
                $scope.setMediaTemplateView('thumbnails', {width: width});
            };

            $scope.setMediaTemplateView = function(templateName, options) {
                if ('thumbnails' != templateName && 'table' != templateName) {
                    $log.error('Unsupported media template view "'+templateName+'"');
                    return;
                }

                if (undefined == options) options= {};

                $scope.mediaTemplateView = templateName;
                if ('thumbnails' == templateName) {
                    $scope.thumbWidth = 150;
                    if (options.width) $scope.thumbWidth = options.width+10;
                    $scope.thumbHeight = $scope.thumbWidth*10/15;
                }

                localStorage.setItem('azimutMediacenter-media-template-view-'+currentFile.id, templateName);
                localStorage.setItem('azimutMediacenter-media-template-view-'+currentFile.id+'-options', angular.toJson(options));

                $scope.$broadcast('mediacenterThumbnailSizeChanged', {
                    width : $scope.thumbWidth,
                    height : $scope.thumbHeight
                });
            };

            $scope.displayMoreMedias = function() {
                if ($scope.mediasDisplayLimit+60 < currentFile.medias.length) $scope.mediasDisplayLimit += 60;
                else $scope.mediasDisplayLimit = currentFile.medias.length;
            };

            $scope.mediasDisplayLimit = 50;

            var storedMediaTemplateView = localStorage.getItem('azimutMediacenter-media-template-view-'+currentFile.id);
            if (storedMediaTemplateView) {
                $log.log('Restoring media template view from local storage', storedMediaTemplateView);
                $scope.setMediaTemplateView(storedMediaTemplateView, angular.fromJson(localStorage.getItem('azimutMediacenter-media-template-view-'+currentFile.id+'-options')));
            }
            else {
                $scope.setMediaTemplateView('thumbnails', {width: 150});
            }

            // handle quick add media

            $scope.forms = new FormsBag();

            $scope.forms.data.simple_media = {
                folder: currentFile.id
            };

            // this will contain the files chosen in the file input field (see azFileInput directive)
            $scope.forms.files.simple_media = [];

            $scope.forms.params.simple_media = {
                submitActive: true,
                submitLabel: Translator.trans('add.files'),
                submitAction: function() {
                    $scope.addMediaFromFile($scope.forms.files.simple_media, $scope.currentFile);
                }
            };

            $scope.forms.data.embed_html_media = {
                folder: currentFile.id
            };

            $scope.forms.params.embed_html_media = {
                submitActive: true,
                submitLabel: Translator.trans('add'),
                submitAction: function() {
                    return $scope.addMediaFromEmbedHtml($scope.forms.data.embed_html_media, $scope.currentFile);
                }
            };

            $scope.addMediaFromFile = function(filesdata, folder) {
                if (!window.FormData) {
                    $log.error("Browser does not support FormData");
                    return false;
                }

                angular.forEach(filesdata, function(file) {
                    MediacenterFileFactory.createMediaFromDroppedFile(file, folder.id).then(function (response) {
                        var media = response.media;

                        $log.info('Media created', response);

                        //NotificationService.addSuccess(Translator.trans('notification.success.media.create'), response);

                        // clear form error messages
                        delete $scope.forms.errors.simple_media;

                        delete $scope.forms.files.simple_media;

                        // TODO: scroll to file
                        $scope.selectFile(media);

                    }, function(response) {
                        $log.error('Media creation failed', response);
                        NotificationService.addError(Translator.trans('notification.error.media.create'), response);

                        // display form error messages
                        if (undefined != response.data.errors) {
                            $scope.forms.errors.simple_media = response.data.errors;
                        }
                    });
                });

            }

            $scope.addMediaFromEmbedHtml = function(formdata, folder) {
                return MediacenterFileFactory.createMediaFromEmbedHtml(formdata, folder.id).then(function (response) {
                    var media = response.media;

                    $log.info('Media created', response);

                    //NotificationService.addSuccess(Translator.trans('notification.success.media.create'), response);

                    // clear form error messages
                    delete $scope.forms.errors.embed_html_media;

                    // remove dirty state on form
                    $scope.forms.params.embed_html_media.formController.$setPristine();

                    // clear input
                    $scope.forms.data.embed_html_media.embed = null;

                    // TODO: scroll to file
                    $scope.selectFile(media);
                }, function(response) {
                    $log.error('Media creation failed', response);
                    NotificationService.addError(Translator.trans('notification.error.media.create'), response);

                    // display form error messages
                    if (undefined != response.data.errors) {
                        $scope.forms.errors.embed_html_media = response.data.errors;
                    }
                });
            }

            $scope.mainContentLoaded();

        }

        if (currentFile.fileType == 'media') {
            var oldFolder = currentFile.parentFile;

            $scope.selectedMediaDeclination = null;
            $scope.selectMediaDeclination = function(mediaDeclination) {
                $scope.selectedMediaDeclination = mediaDeclination;
            };

            $scope.formLocale = $rootScope.locale;

            $scope.forms = new FormsBag();

            $scope.mediaEditIsGranted = null;

            $scope.setMainDeclination = function(mainDeclination) {
                if (undefined != $scope.mainDeclination) $scope.mainDeclination.isMainDeclination = false;
                $scope.mainDeclination = mainDeclination;
                $scope.mainDeclinationPath = mainDeclination.mediaDeclinationType.path;
                $scope.mainDeclinationThumb = mainDeclination.mediaDeclinationType.thumb;
            }

            var media = currentFile;
            var mediaDeclinations = media.declinations;

            if (media.declinations.length > 1) $scope.formMediaTemplateUrl = Routing.generate('azimut_mediacenter_backoffice_jsview_media_form',{ type: media.mediaType });
            else $scope.formMediaTemplateUrl = Routing.generate('azimut_mediacenter_backoffice_jsview_media_form_with_one_declination',{ type: media.mediaType });

            $scope.formMediaDeclinationTemplateUrl = Routing.generate('azimut_mediacenter_backoffice_jsview_media_declination_form',{ type: media.mediaType });


            if (oldFolder != media.parentFile) {
                $log.info('The media has been moved by the server, it is now into "'+media.folder.name+'"');
                NotificationService.addInfo(Translator.trans('notification.info.media.moved.by.server.in.%folder_name%', { 'folder_name' : media.folder.name }));
            }

            // we don't use the real MediacenterFile object because we need raw data to be binded into the form
            var mediaFormdata = media.toFormData();

            var mediaDeclinationsFormdata = mediaFormdata.mediaDeclinations;
            delete mediaFormdata.mediaDeclinations;

            //if there is only one declination, we add it to the form data
            if (mediaDeclinationsFormdata.length == 1) {
                mediaFormdata.mediaDeclinations = mediaDeclinationsFormdata;

                if (undefined != mediaFormdata.mediaDeclinations[0].mediaDeclinationType.embedHtml) {
                    mediaFormdata.mediaDeclinations[0].mediaDeclinationType.isEmbeddedMedia = true;
                }
            }

            $scope.mediaDeclinations = mediaDeclinationsFormdata;

            $scope.setMainDeclination($filter('filter')(mediaDeclinationsFormdata, {isMainDeclination: true})[0]);

            $scope.mediaTypeName = Translator.trans(currentFile.mediaType);

            $scope.forms.data.media = mediaFormdata;

            $scope.forms.params.media = {
                submitActive: true,
                submitLabel: Translator.trans('update.media'),
                cancelLabel: Translator.trans('cancel'),
                submitAction: function() {
                    return $scope.saveFile(media, $scope.forms.data.media, $scope.forms.files.media);
                },
                cancelAction: function() {
                    //redirect to parent folder
                    $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail', {filePath: currentFile.parentFile.getFullpath()});
                },
                confirmDirtyDataStateChangeMessage: Translator.trans('media.has.not.been.saved.are.you.sure.you.want.to.continue')
            };

            $scope.mediaEditIsGranted = response.data.mediaEditIsGranted;

            $scope.mainContentLoaded();

            $scope.newMediaDeclination = function(media,mediaDeclinationType) {
                if ('media' != media.fileType) {
                    $log.error('Cannot create a media declination inside other object type than "media"');
                    return;
                }
                $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.new_media_declination',{
                    filePath: media.getFullpath(),
                    mediaDeclinationType: mediaDeclinationType
                });
            };

            $scope.showMediaPublications = false;
            $scope.toggleMediaPublications = function() {
                $scope.showMediaPublications = !$scope.showMediaPublications;
            };

            MediacenterFileFactory.getFilePublications(media.id).then(function(response) {
                $scope.mediaPublications = response.data.publications;
            });
        }

        $scope.setCurrentFile(currentFile);

        //$scope.searchFilterTypeValues = ['','folder']
        //$scope.searchFilterTypeValues.concat($scope.availableMediaTypes);

         //listen to name change (and update the parent view ?)
         //$scope.$watch('file.name',function(newName){
             //$log.debug("filename watcher : file name has changed : "+newName);
             //$log.debug($scope.$parent.files);
             //$scope.$parent.files[$scope.file.id].name = newName;
             //$scope.$parent.$digest()
         //});

        $scope.renameFile = function(file, newName) {
            if (!(file instanceof MediacenterFile)) {
                $log.error('File to rename must be an instance of MediacenterFile');
                return false;
            }

            file.editMode = false;

            // abort if no change
            if (file.name == newName) return;

            var updatedFileData = {
                id: file.id,
                name: newName
            };

            $scope.saveFile(file, updatedFileData, null, 'patch', true).then(function(response) {
                NotificationService.addSuccess(Translator.trans('notification.success.'+ file.fileType +'.rename'), response);
            }, function(response) {
                NotificationService.addError(Translator.trans('notification.error.'+ file.fileType +'.rename'), response, { 'flatenFormErrors': true });
            });
        }

        $scope.saveFile = function(file, fileData, uploadfilesData, method, disableNotifications) {
            var promise = null;

            if (!file.fileType) {
                $log.error('Error : file object type unspecified');
                return false;
            }

            var oldParentFile = file.parentFile;;

            if ('folder' == file.fileType) {
                promise = MediacenterFileFactory.updateFolder(fileData, method).then(function(response) {
                    if (oldParentFile.id != response.data.folder.parentFolderId) {
                        $log.info('Folder has been moved by server');
                        NotificationService.addInfo(Translator.trans('notification.info.folder.moved.by.server'));
                    }

                    if (!disableNotifications) NotificationService.addSuccess(Translator.trans('notification.success.folder.update'));
                }, function(response) {
                    $log.error('Update folder failed: ', response);
                    if (!disableNotifications) NotificationService.addError(Translator.trans('notification.error.folder.update'), response);
                    // forward rejection
                    return $q.reject(response);
                });
            }

            else if ('media' == file.fileType) {
                if (!window.FormData) {
                    $log.error("Browser does not support FormData");
                    return false;
                }

                if (null != uploadfilesData && uploadfilesData.length>0) {
                    //TODO: search for a way to automate name building ".mediaDeclinations[0].file"
                    fileData.mediaDeclinations[0].file = uploadfilesData[0];
                }

                promise = MediacenterFileFactory.updateMedia(fileData, method).then(function(response) {
                    var media = response.data.media;
                    $log.info('Media updated', response);

                    // remove dirty state on form
                    if (undefined != $scope.forms.params.media && undefined != $scope.forms.params.media.formController) {
                        $scope.forms.params.media.formController.$setPristine();
                    }

                    if (oldParentFile.id != media.parentFile.id) {
                        $log.info('The media has been moved by server');
                        NotificationService.addInfo(Translator.trans('notification.info.media.moved.by.server'));
                    }

                    //redirect to parent folder
                    $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.file_detail',{filePath: media.parentFile.getFullpath()});

                    if (!disableNotifications) NotificationService.addSuccess(Translator.trans('notification.success.media.update'), response);

                    // clear form error messages
                    delete $scope.forms.errors.media

                }, function(response) {
                    $log.error('Update media failed', response);
                    if (!disableNotifications) NotificationService.addError(Translator.trans('notification.error.media.update'), response);

                    // display form error messages
                    if (undefined != response.data.errors) {
                        $scope.forms.errors.media = response.data.errors;
                    }

                    // forward rejection
                    return $q.reject(response);
                });
            }
            else {
                $log.error('Cannot save file, object type "'+file.fileType+'" is not supported');
            }

            return promise;
        }

        $scope.addFolder = function(parentFolder) {
            var newFileNameBase = "New folder";
            var newFileName = newFileNameBase;
            var newFileNameSuffixNumber = 0;

            //if a folder has the same name, add an incremental number at the end
            if (parentFolder.subfolders) {
                while (ArrayExtra.findFirstInArray(parentFolder.subfolders,{name: newFileName}) != null) {
                    newFileNameSuffixNumber++;
                    newFileName = newFileNameBase +" "+ newFileNameSuffixNumber;
                }
            }

            var file = {
                name: newFileName,
                parentFolder: parentFolder.id
            };

            MediacenterFileFactory.createFolder(file).then(function (response) {
                var folder = response.data.folder;
                folder.editMode = true;

                $scope.selectFile(folder);

                //TODO : scroll to folder
                //....
            }, function(response) {
                $log.error('Unable to create folder' + response.data);
                NotificationService.addError(Translator.trans('notification.error.folder.create'), response);
            });
        }

        $scope.newMedia = function(folder,mediaType) {
            if ('folder' != folder.fileType) {
                $log.error('Cannot create a media inside another one, parent file has to be of type "folder"');
                return;
            }
            $state.go($scope.mediacenterParams.statePrefix+'.mediacenter.new_media',{
                filePath: folder.getFullpath(),
                mediaType: mediaType
            });
        };

        $scope.deleteMediaDeclination = function(mediaDeclination) {
            $log.log("deleting media declination", mediaDeclination);

            MediacenterFileFactory.deleteMediaDeclination(mediaDeclination).then(function (response) {
                $scope.mediaDeclinations.splice($scope.mediaDeclinations.indexOf(mediaDeclination), 1);
                NotificationService.addSuccess(Translator.trans('notification.success.media.declination.delete'), response);
            }, function(response) {
                $log.error('Error while deleting media declination ', response);
                NotificationService.addError(Translator.trans('notification.error.media.declination.delete'), response);
            });
        };
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.file.%file_path%.get', { 'file_path' : $stateParams.filePath }));
        $scope.$parent.showContentView = false;
    });
}]);
