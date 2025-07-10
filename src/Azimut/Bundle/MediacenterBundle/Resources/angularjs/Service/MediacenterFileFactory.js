/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:54:28
 *
 ******************************************************************************
 *
 * File factory : handle file and folder objects with the backend API
 *
 */

'use strict';
angular.module('azimutMediacenter.service')

.factory('MediacenterFileFactory', [
'$log', '$http', '$rootScope', 'formDataObject', 'ArrayExtra', '$q', 'BackofficeDiskQuotaFactory', 'ObjectExtra', 'MediacenterFile', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $rootScope, formDataObject, ArrayExtra, $q, BackofficeDiskQuotaFactory, ObjectExtra, MediacenterFile, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('MediacenterFileFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_mediacenter_api_';

    factory.isGrantedUser = false;

    factory.maxUploadFileSize = 2000 * 1000 * 1000; // set max upload file size to 2000M on js side

    // files contain the whole file structure (folders and medias)
    factory.files = [];

    // index array for retrieving a folder by id from files object
    factory.foldersIndex = [];

    // index array for retrieving a media by id from files object
    factory.mediasIndex = [];

    factory.availableMediaTypes = null;

    // uploading files list
    factory.filesWaitingForUpload = [];

    // this folder will maintain its media cache
    factory.lastOpenedFolder = null;


    /*** privates functions ***/

    /**
     * - Transforms the fileData object (fetched from server api) into a MediacenterFile
     * - Update Files hierarchy
     * - Loop over file hierarchy to create (or update) a each child File
     *
     * parentFile is optional, if not provided, it will be fetched from fileData
     * (fileData.parentFolderId for folders or fileData.folder.id for media)
     *
     * refreshDate is optional, this is usefull to force an identical date to all files when
     * fetching a list
     */
    factory.parseFile = function(fileData, parentFile, refreshDate) {
        var fileType = MediacenterFile.guessFileType(fileData);

        // if parentFile is not explicitely provided, fetch it from the fileData object
        if (undefined === parentFile) {
            if ('folder' == fileType && fileData.parentFolderId) {
                //TODO: what if media folder doesn't exists in index => create it or trigger cache refresh?
                if (!factory.foldersIndex[fileData.parentFolderId]) $log.error('Folder with id '+ fileData.parentFolderId +' does not exist in index (TODO: create it)');
                parentFile = factory.foldersIndex[fileData.parentFolderId];
            }
            if ('media' == fileType) {
                if (!fileData.folder.id) {
                    $log.error("Media file must have a parent folder");
                    return false;
                }
                //TODO: what if media folder doesn't exists in index => create it or trigger cache refresh?
                if (!factory.foldersIndex[fileData.folder.id]) $log.error('Folder with id '+ fileData.folder.id +' does not exist in index (TODO: create it)');
                parentFile = factory.foldersIndex[fileData.folder.id];
            }
        }

        var file;

        // find existing file in index
        if ('folder' == fileType) {
            file = factory.foldersIndex[fileData.id];

            // retain folder medias cache if new data doesn't have medias
            if (undefined != file && undefined == fileData.medias) {
                fileData.medias = file.medias;
            }
        }
        else {
            file = factory.mediasIndex[fileData.id];
        }

        var newFile = new MediacenterFile(fileData, parentFile);

        // it is a new file
        if (undefined == file) {
            file = newFile;

            if ('folder' == file.fileType) {
                //add the new folder to subfolders in parent file object (if it is not a root folder)
                if (null != file.parentFile) {
                    // add the formated MediacenterFile object (if not existing)
                    if (-1 == file.parentFile.subfolders.indexOf(file)) {
                        file.parentFile.subfolders.push(file);
                    }
                }
                else {
                    factory.files.push(file);
                }
            }
            else {
                // add the new media to medias in parent file object (if not existing)
                if (-1 == file.parentFile.medias.indexOf(file)) file.parentFile.medias.push(file);
            }
        }
        // it is an update of an existing file
        else {
            var oldParentFile = file.parentFile;

            if ('folder' == file.fileType) {
                var showSubfolders = file.showSubfolders;
            }

            // copy new object properties into object in the index (clearing original object before)
            ObjectExtra.shallowCopy(newFile, file);

            // update hierarchy linkage if it has been moved server side in the meantime
            if ('folder' == file.fileType) {

                // keep original showSubfolders property
                file.showSubfolders = showSubfolders;

                // remove subfolder from old folder (if it wasn't a root folder)
                if (null != oldParentFile && oldParentFile != file.parentFile) {
                    oldParentFile.subfolders.splice(oldParentFile.subfolders.indexOf(file), 1);
                }

                // add subfolder in new folder (if it not a root folder)
                if (null != file.parentFile) {
                    if (-1 == file.parentFile.subfolders.indexOf(file)) file.parentFile.subfolders.push(file);
                }
            }
            else {
                // remove media from old folder
                if (oldParentFile != file.parentFile) oldParentFile.medias.splice(oldParentFile.medias.indexOf(file), 1);

                // add media in new folder
                if (-1 == file.parentFile.medias.indexOf(file)) file.parentFile.medias.push(file);
            }
        }
        // if the file is a folder
        if ('folder' == file.fileType) {
            // update index
            factory.foldersIndex[file.id] = file;

            // here subfolders and medias are raw data, we strip them and then inject the parsed MedicenterFiles
            file.subfolders = [];
            file.medias = [];

            // loop over hierarchy to create each complete path
            if (undefined != fileData.subfolders) {
                for (var i=0; i<fileData.subfolders.length; i++) {
                    factory.parseFile(fileData.subfolders[i], file, refreshDate);
                }
            }

            if (undefined != fileData.medias) {
                for (var i=0; i<fileData.medias.length; i++) {
                    factory.parseFile(fileData.medias[i], file, refreshDate);
                }
            }

            // inject uploading files in queue in folder's medias
            for (var i=0; i<factory.filesWaitingForUpload.length; i++) {
                if (factory.filesWaitingForUpload[i].folder == file.id) {
                    file.medias.push(factory.filesWaitingForUpload[i]);
                }
            }
        }
        // if the file is a media
        else {
            // update index
            factory.mediasIndex[file.id] = file;

            file.cssIcon = ArrayExtra.findFirstInArray(factory.availableMediaTypes,{id: file.mediaType}).cssIcon;
        }

        if (refreshDate) file.refreshDate = refreshDate;

        return file;
    };

    factory.getAvailableTypesFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_media_availabletypes')).then(function (response) {
            var mediaTypes = response.data.types;

            // translate type name
            for (var i=0;i<mediaTypes.length;i++) {
                mediaTypes[i].name = Translator.trans(mediaTypes[i].id);
            }

            factory.availableMediaTypes = mediaTypes;
        });

        return promise;
    };

    factory.getFilesFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_folders')).then(function (response) {
            var refreshDate = new Date();

            // modify received datas
            for (var i=0; i<response.data.folders.length; i++) {
                // parse file (add file object methods and build indexes)
                factory.files[i] = factory.parseFile(response.data.folders[i], null, refreshDate);
            }

            // clean indexes
            for (var i=0; i<factory.foldersIndex; i++) {
                if (factory.foldersIndex[i].refreshDate != refreshDate) {
                    delete factory.foldersIndex[i];
                }
            }
            for (var i=0; i<factory.mediasIndex; i++) {
                if (factory.mediasIndex[i].refreshDate != refreshDate) {
                    delete factory.mediasIndex[i];
                }
            }

            // maintain and update media cache for last opened folder
            if (null != factory.lastOpenedFolder) {
                factory.getFolder(factory.lastOpenedFolder.id, 'all');
            }

            // when data fetched, return it to populate the promise
            return factory.files;
        });

        return promise;
    };

    factory.refreshCache = function() {
        return factory.getFilesFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    };

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if (ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || -1 == $state.current.name.indexOf('.mediacenter.') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh');

        return factory.refreshCache();
    };

    factory.addWaitingUploadFile = function(name, filename, folderId, parentMediaId) {
        var fileWaitingForUpload = {
            'name': name,
            'fileType': 'temp',
            'filename': filename,
            'progress': 0,
            'status': 'waiting',
            'waitingForUpdate': true,
            'hasUploadingFile': true,
            'folder': folderId,
            'parentMedia': parentMediaId
        }
        factory.filesWaitingForUpload.push(fileWaitingForUpload);

        // add temp file to folder's medias
        if (folderId) factory.foldersIndex[folderId].medias.push(fileWaitingForUpload);

        return fileWaitingForUpload;
    };

    factory.removeWaitingUploadFile = function(fileWaitingForUpload) {
        if (fileWaitingForUpload.folder) factory.foldersIndex[fileWaitingForUpload.folder].medias.splice(factory.foldersIndex[fileWaitingForUpload.folder].medias.indexOf(fileWaitingForUpload), 1);
        factory.filesWaitingForUpload.splice(factory.filesWaitingForUpload.indexOf(fileWaitingForUpload), 1);
    };

    factory.updateWaitingUploadFileProgress = function(fileWaitingForUpload, update) {
        fileWaitingForUpload.status = 'uploading';

        var uploadPercent = Math.round(update.loaded * 100 / update.total)

        fileWaitingForUpload.progress = uploadPercent;

        if (uploadPercent == 100) fileWaitingForUpload.status = 'processing file';
    };

    factory.checkFileSize = function(file, deferred) {
        if (file.size > factory.maxUploadFileSize){
            $log.warn('File size exceeded '+factory.maxUploadFileSize+' limit (trying to upload '+ file.size +')');
            deferred.reject({data: {
                error: {
                    message: Translator.trans('file.size.exceeded.%max_size%.limit', { 'max_size' : (factory.maxUploadFileSize/1000/1000) + 'M' })
                }
            }});
            return false;
        }
        return true;
    };

    factory.getFolder = function(id, locale) {
        if (null == locale) locale = $rootScope.locale;

        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_folder', {id: id})+'?locale='+locale).then(function (response) {
            var folder = response.data.folder;

            folder.isCompleteObject = true;

            // subfolders are not included when fetching complete object, restore them from cache
            folder.subfolders = angular.copy(factory.foldersIndex[folder.id].subfolders);

            folder = factory.parseFile(folder);

            // include folder's media in cache
            for (var i = response.data.folder.medias.length - 1; i >= 0; i--) {
                factory.parseFile(response.data.folder.medias[i], folder);
            }

            response.data.folder = folder;

            return response;

        });

        return promise;
    };

    factory.getMedia = function(id, locale) {
        if (null == locale) locale = $rootScope.locale;

        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_media', {id: id})+'?locale='+locale).then(function (response) {

            var media = response.data.media;

            media.isCompleteObject = true;

            media = factory.parseFile(media);

            // update response
            response.data.media = media;

            // when data fetched, return it to populate the promise
            return response;
        });

        return promise;
    };

    factory.findFileFromPathArray = function(filePathArray) {
        if (!angular.isArray(filePathArray)) {
            throw "findFileFromPathArray argument must be of type Array";
        }

        // search in cache
        // find the first file that has a corresponding path attribute (here 'getPath' because it's a computed value)
        var file = ArrayExtra.findFirstInArray(factory.files,{getPath: filePathArray[0]});

        if (file) {
            // browse file hierarchy to find the current file to display
            for (var i = 1; i < filePathArray.length; i++) {
                var subfile = null;

                if (file.subfolders.length>0) {
                    subfile = ArrayExtra.findFirstInArray(file.subfolders,{getPath: filePathArray[i]});
                    // automaticaly expand parent subfolders
                    if (subfile && subfile.parentFile) subfile.parentFile.showSubfolders = true;
                }
                if (!subfile && file.medias.length>0) {
                    subfile = ArrayExtra.findFirstInArray(file.medias,{getPath: filePathArray[i]});
                }
                if (!subfile) return null;
                file = subfile;
            }
        }

        return file;
    };

    factory.getFilePublications = function(id) {
        return $http.get(Routing.generate(factory.urlPrefix + 'get_media_publications', {id: id}));
    }

    /*** end privates functions ***/


    /*** public functions ***/
    return {

        // init service (constructor)
        init: function() {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if (factory.initialized) {
                factory.getAvailableTypesFromServer();
                factory.refreshCache();
                deferred.resolve();
            }
            else {
                factory.getAvailableTypesFromServer().then(function(response) {
                    factory.refreshCache().then(function(response) {
                        factory.isGrantedUser = true;

                        factory.initialized = true;

                        // schedule auto cache refresh
                        $interval.cancel(refreshIntervalPromise);
                        refreshIntervalPromise = $interval(factory.autoRefreshCache, factory.autoCacheRefreshDelay*60*1000);

                        deferred.resolve();
                    }, function(response) {
                        // if api access is forbidden or unauthorized
                        if (401 == response.data.error.code || 403 == response.data.error.code) {
                            factory.isGrantedUser = false;
                            // resolve instead of reject, instead this will be blocking, we want the controller to be called all the time so we can handle a redirect
                            deferred.resolve();
                        }
                        else {
                            deferred.reject(response);
                        }
                    });
                }, function(response) {
                    // if api access is forbidden or unauthorized
                    if (401 == response.data.error.code || 403 == response.data.error.code) {
                        factory.isGrantedUser = false;
                        // resolve instead of reject, instead this will be blocking, we want the controller to be called all the time so we can handle a redirect
                        deferred.resolve();
                    }
                    else {
                        deferred.reject(response);
                    }
                });
            }

            return deferred.promise;
        },

        files: function() {
            return factory.files
        },

        findFolder: function(id) {
            return factory.foldersIndex[id];
        },

        findMedia: function(id) {
            return factory.mediasIndex[id];
        },

        filesWaitingForUpload: function() {
            return factory.filesWaitingForUpload;
        },

        getTrashedFiles: function() {
            return $http.get(Routing.generate(factory.urlPrefix+'get_trashbin')).then(function(response){

                if (response.data.medias) {
                    for (var i=0;i<response.data.medias.length;i++) {
                        response.data.medias[i].fileType = 'media';
                    }
                }
                if (response.data.folders) {
                    for (i=0;i<response.data.folders.length;i++) {
                        response.data.folders[i].fileType = 'folder';
                    }
                }

                return response;
            });
        },

        deleteTrashedFiles: function() {
            return $http.delete(Routing.generate(factory.urlPrefix+'delete_trashbin')).then(function(){
                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();
            });
        },

        availableMediaTypes:  function() {
            return factory.availableMediaTypes;
        },

        createFolder: function(folderData) {
            if (folderData instanceof MediacenterFile) {
                $log.error('folderData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            return $http.post(Routing.generate(factory.urlPrefix+'post_folders'), {folder: folderData}).then(function (response) {
                var folder = response.data.folder;
                var parentFolder = factory.foldersIndex[folder.parentFolder];
                // parse folder (add file object methods)
                folder = factory.parseFile(folder, parentFolder);
                // update response
                response.data.folder = folder;

                // when data fetched, return it to populate the promise
                return response;
            });
        },

        createMedia: function(mediaData) {
            if (mediaData instanceof MediacenterFile) {
                $log.error('mediaData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            var deferred = $q.defer();

            for (var i=0; i<mediaData.mediaDeclinations.length; i++) {
                if (undefined != mediaData.mediaDeclinations[i].file && !factory.checkFileSize(mediaData.mediaDeclinations[i].file, deferred)) {
                    return deferred.promise;
                }
            }

            var progressListener = function(deferred) {
                return function(event) {
                    deferred.notify({
                        'updateType': 'progress',
                        'loaded': event.loaded,
                        'total': event.total
                    });
                };
            };

            mediaData = ObjectExtra.deleteUndefinedProperties(mediaData);

            // convert data into a FormData object
            var formData = formDataObject({
                'media': mediaData
            });

            /*
            // $http is NOT able to track upload progress : https://github.com/angular/angular.js/issues/1934
            $http.post(Routing.generate(factory.urlPrefix+'post_media'), formData, {
                transformRequest: angular.identity, // says to angular not to serialize data
                headers: {'Content-Type': undefined}
            })
            .then(function(update) {
            });
            */

            // CAUTION: this won't be linked to any of the interceptors configured on $http service
            // This is the reason why we called ObjectExtra.deleteUndefinedProperties(mediaData) just before
            $.ajax({
                type: 'POST',
                url: Routing.generate(factory.urlPrefix+'post_media'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(responseData, textStatus, jqXHR) {
                    deferred.resolve({data: responseData});
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    deferred.reject({data: jqXHR.responseJSON});
                },
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                       xhr.upload.addEventListener('progress', progressListener(deferred), false);
                    } else {
                        $log.warn('Upload progress is not supported.');
                    }
                    return xhr;
                }
            });

            var fileWaitingForUpload = factory.addWaitingUploadFile(mediaData.name, mediaData.mediaDeclinations[0].file, mediaData.folder);

            deferred.promise.then(function(response) {
                var media = response.data.media;

                media.isCompleteObject = true;

                // parse media (add file object methods)
                media = factory.parseFile(media);

                // update response
                response.media = media;

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();

                return response;

            }, function(response) {
                $log.error('Failed to upload file: ', response);
                fileWaitingForUpload.status = 'error';

                return response;
            }, function(update) {
                if (update.updateType == 'progress') {
                    factory.updateWaitingUploadFileProgress(fileWaitingForUpload, update);
                }
            })
            .finally(function() {
                // remove file name from upload queue
                factory.removeWaitingUploadFile(fileWaitingForUpload);
            });

            return deferred.promise;

            /*var promise = $http.post(Routing.generate(factory.urlPrefix+'post_media'), {media: media}).then(function (response) {
                var media = response.data.media;
                var folder = null;

                //TODO : handle error
                if (!media.folder.id) $log.error("Media folder id should be defined");

                //retrieve folder object from the js structure
                folder = factory.foldersIndex[media.folder.id];

                //parse media (add file object methods)
                media = factory.parseFile(media,folder);

                //add the new media to medias in parent file object
                folder.medias.push(media);

                //when data fetched, return it to populate the promise
                return response;
            });

            return promise;*/
        },

        createMediaFromDroppedFile: function(file, folderId) {
            // $http does not provide support for progress events
            // see https://github.com/angular/angular.js/issues/1934
            // so we use a custom construction to support that
            var deferred = $q.defer();

            if (!factory.checkFileSize(file, deferred)) {
                return deferred.promise;
            }

            var progressListener = function(deferred) {
                return function(event) {
                    deferred.notify({
                        'updateType': 'progress',
                        'loaded': event.loaded,
                        'total': event.total
                    });
                };
            };

            // convert datas into a FormData object
            var formData = formDataObject({
                'simple_media': {
                    'upload': file,
                    'folder': folderId
                }
            });

            $.ajax({
                type: 'POST',
                url: Routing.generate(factory.urlPrefix+'post_mediafromfile'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(responseData, textStatus, jqXHR) {
                    deferred.resolve({data: responseData});
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    deferred.reject({data: jqXHR.responseJSON});
                },
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                       xhr.upload.addEventListener('progress', progressListener(deferred), false);
                    } else {
                        $log.warn('Upload progress is not supported.');
                    }
                    return xhr;
                }
            });

            var uploadFileName = file.name;
            uploadFileName = uploadFileName.replace(/[_\-\.]/g, ' ');

            var fileWaitingForUpload = factory.addWaitingUploadFile(uploadFileName, file.name, folderId);

            deferred.promise.then(function (response) {
                var media = response.data.media;

                media.isCompleteObject = true;

                // parse media (add file object methods)
                media = factory.parseFile(media);

                // update response
                response.data.media = media;

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();

                // when data fetched, return it to populate the promise
                return response;
            }, function(response) {
                $log.error('Failed to upload file: ', response);
                fileWaitingForUpload.status = 'error';

                return response;
            }, function(update) {
                if (update.updateType == 'progress') {
                    factory.updateWaitingUploadFileProgress(fileWaitingForUpload, update);
                }
            })
            .finally(function() {
                // remove file name from upload queue
                factory.removeWaitingUploadFile(fileWaitingForUpload);
            });

            return deferred.promise;

            /*
            var promise = $http({
                method: 'POST',
                url: Routing.generate(factory.urlPrefix+'post_mediafromfile'),
                headers: {
                    'Content-Type': undefined
                },
                data: {
                    'simpleMedia': {
                        'upload': file,
                        'folder': folderId
                    }
                },
                transformRequest: function(data) {
                    return formDataObject(data);
                }
            }).then(function (response) {
                var media = response.data.media;
                var folder = null;

                //TODO : handle error
                if (!media.folder.id) $log.error("media folder id should be defined");

                //retrieve folder object from the js structure
                folder = factory.foldersIndex[media.folder.id];

                //parse media (add file object methods)
                media = factory.parseFile(media,folder);

                //add the new media to medias in parent file object
                folder.medias.push(media);

                //when data fetched, return it to populate the promise
                return response;
            });

            // Return the promise
            return promise;
            */
        },

        createMediaFromEmbedHtml: function(formdata, folder) {
            var promise = $http.post(Routing.generate(factory.urlPrefix+'post_mediafromembedhtml'), {embed_html_media: formdata}).then(function (response) {
                var media = response.data.media;

                media.isCompleteObject = true;

                // parse media (add file object methods)
                media = factory.parseFile(media);

                // update response
                response.data.media = media;

                // when data fetched, return it to populate the promise
                return response;
            }, function(response) {
                $log.error('Failed to create file from embed html: ', response);
                // forward rejection
                return $q.reject(response);
            });

            return promise;
        },

        createMediaDeclination: function(mediaDeclinationData) {
            if (mediaDeclinationData instanceof MediacenterFile) {
                $log.error('mediaDeclinationData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            var deferred = $q.defer();

            var progressListener = function(deferred) {
                return function(event) {

                    deferred.notify({
                        'updateType': 'progress',
                        'loaded': event.loaded,
                        'total': event.total
                    });
                    $log.log('progress : ', Math.round(event.loaded * 100 / event.total));
                };
            };

            mediaDeclinationData = ObjectExtra.deleteUndefinedProperties(mediaDeclinationData);

            // convert datas into a FormData object
            var formData = formDataObject({
                'media_declination': mediaDeclinationData
            });

            $.ajax({
                type: 'POST',
                url: Routing.generate(factory.urlPrefix+'post_mediadeclinations'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(responseData, textStatus, jqXHR) {
                    deferred.resolve({data: responseData});
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    deferred.reject({data: jqXHR.responseJSON});
                },
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                       xhr.upload.addEventListener('progress', progressListener(deferred), false);
                    } else {
                        $log.warn('Upload progress is not supported.');
                    }
                    return xhr;
                }
            });

            var fileWaitingForUpload = factory.addWaitingUploadFile(mediaDeclinationData.name, mediaDeclinationData.file, null, mediaDeclinationData.media);

            deferred.promise.then(function (response) {
                // update thumbnail of media
                var mediaDeclination = response.data.mediaDeclination;

                //TODO: add this verification in all similar methods
                if (null == mediaDeclination) $log.error('got null object from server api, expected mediaDeclination object');

                if (mediaDeclination.isMainDeclination) factory.mediasIndex[mediaDeclination.media.id].thumb = mediaDeclination.thumb;

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();

                // when data fetched, return it to populate the promise
                return response;

            }, function(response) {
                $log.error('Failed to upload file: ', response);
                fileWaitingForUpload.status = 'error';

                return response;
            }, function(update) {
                if (update.updateType == 'progress') {
                    factory.updateWaitingUploadFileProgress(fileWaitingForUpload, update);
                }
            })
            .finally(function() {
                // remove file name from upload queue
                factory.removeWaitingUploadFile(fileWaitingForUpload);
            });

            return deferred.promise;

            /*
            var promise = $http({
                method: 'POST',
                url: Routing.generate(factory.urlPrefix+'post_mediadeclinations'),
                headers: {
                    'Content-Type': undefined
                },
                data: {
                    'mediaDeclination': mediaDeclination
                },
                transformRequest: function(data) {
                    //transform request to be able to send files
                    return formDataObject(data);
                }
            }).then(function (response) {
                //update thumbnail of media
                var mediaDeclination = response.data.mediaDeclination;
                if (mediaDeclination.isMainDeclination) factory.mediasIndex[mediaDeclination.media.id].thumb = mediaDeclination.thumb;
            });

            return promise;*/
        },

        getFolder: factory.getFolder,

        getMedia: factory.getMedia,

        updateFolder: function(folderData, method) {
            var promise;
            if (undefined != method) method = method.toUpperCase();
            if (method != 'PATCH') method = 'PUT';

            if (folderData instanceof MediacenterFile) {
                $log.error('folderData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            var folder = factory.foldersIndex[folderData.id];
            folder.waitingForUpdate = true;

            // work on a copy of folder
            var folderPut = {
                name: folderData.name,
                parentFolder: folderData.parentFolder,
            }

            promise = $http({
                method: method,
                url: Routing.generate(factory.urlPrefix+'patch_folder',{ id: folderData.id }),
                data: { folder: folderPut }
            });

            promise.then(function (response) {
                var folder = response.data.folder;

                folder = factory.parseFile(folder);

                // update response
                response.data.folder = folder;

                //when data fetched, return it to populate the promise
                return response;
            })
            .finally(function() {
                folder.waitingForUpdate = false;
            });

            return promise;
        },

        refreshFolderSize: function(folder) {
            return $http.get(Routing.generate(factory.urlPrefix+'get_folder', {id: folder.id})).then(function (response) {
                folder.size = response.data.folder.size;
                return response;
            });
        },

        // update media with given mediaData
        updateMedia: function(mediaData, method) {
            if (mediaData instanceof MediacenterFile) {
                $log.error('mediaData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            var promise = null;
            var fileWaitingForUpload = null;

            if (undefined != method) method = method.toUpperCase();
            if (method != 'PATCH') method = 'PUT';

            var media = factory.mediasIndex[mediaData.id];

            if (undefined == media) {
                $log.error("Can't find media with id "+ mediaData.id +" in index");
                return false;
            }

            media.waitingForUpdate = true;

            //work on a copy of media
            var mediaDataPut = {
                name: mediaData.name,
                description: mediaData.description,
                folder: mediaData.folder,
                mediaType: ObjectExtra.deepCopy(mediaData.mediaType),
            }

            //work on copies of declinations
            if (mediaData.mediaDeclinations) {
                mediaDataPut.mediaDeclinations = [];
                angular.forEach(mediaData.mediaDeclinations, function(mediaDeclination, key){
                    $log.log(mediaDeclination.file);
                    mediaDataPut.mediaDeclinations[key] = {
                        name: mediaDeclination.name,
                        file: mediaDeclination.file,
                        mediaDeclinationType: ObjectExtra.deepCopy(mediaDeclination.mediaDeclinationType)
                    }

                    // remove extra fields
                    // TODO: replace this by a list of autorized fields, or delete these lines and allow extra data on api
                    delete mediaDataPut.mediaDeclinations[key].mediaDeclinationType.fileExtension;
                    delete mediaDataPut.mediaDeclinations[key].mediaDeclinationType.path;
                    delete mediaDataPut.mediaDeclinations[key].mediaDeclinationType.thumb;

                    // do not send file if it hasn't changed
                    if (!(mediaDataPut.mediaDeclinations[key].file instanceof File)) {
                        $log.log('file has not changed, removing from update request');
                        delete mediaDataPut.mediaDeclinations[key].file;
                    }
                });
            }

            var deferred = $q.defer();

            var progressListener = function(deferred) {
                return function(event) {

                    deferred.notify({
                        'updateType': 'progress',
                        'loaded': event.loaded,
                        'total': event.total
                    });
                    $log.log('progress : ',Math.round(event.loaded * 100 / event.total));
                };
            };

            //if media is a complete object (with all media specific fields)
            //if (media.isCompleteObject) {

                mediaDataPut = ObjectExtra.deleteUndefinedProperties(mediaDataPut);

                // convert datas into a FormData object
                var mediaFormData = formDataObject({
                    'media': mediaDataPut,
                    '_method': method
                });

                $.ajax({
                    type: 'POST',
                    url: Routing.generate(factory.urlPrefix+'put_media',{ id: mediaData.id }),
                    data: mediaFormData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(responseData, textStatus, jqXHR) {
                        deferred.resolve({data: responseData});
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        deferred.reject({data: jqXHR.responseJSON});
                    },
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                           xhr.upload.addEventListener('progress', progressListener(deferred), false);
                        } else {
                            $log.warn('Upload progress is not supported.');
                        }
                        return xhr;
                    }
                });

                if (undefined != mediaData.mediaDeclinations && mediaData.mediaDeclinations.length>0 && null != mediaData.mediaDeclinations[0].file) fileWaitingForUpload = factory.addWaitingUploadFile(mediaData.name, mediaData.mediaDeclinations[0].file, mediaData.folder);

                promise = deferred.promise;
            /*}

            // if media is a partial object, then we update only known properties
            else {
                // work on a copy of media
                var mediaPatch = {
                    name: mediaData.name,
                    folder: mediaData.folder
                }

                // there is no shortcut method for patch
                //promise = $http.patch(Routing.generate(factory.urlPrefix+'patch_media',{ id: mediaData.id }), {media: mediaPatch});

                promise = $http({
                    method: 'PATCH',
                    url: Routing.generate(factory.urlPrefix+'patch_media',{ id: mediaData.id }),
                    data: { media: mediaPatch }
                });
            }*/

            promise.then(function (response) {
                var media = response.data.media;

                media.isCompleteObject = true;

                media = factory.parseFile(media);

                // update response
                response.data.media = media;

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();

                // when data fetched, return it to populate the promise
                return response;
            })
            .finally(function() {
                media.waitingForUpdate = false;
                // remove file name from upload queue
                if (null != fileWaitingForUpload) factory.removeWaitingUploadFile(fileWaitingForUpload);
            });

            return promise;
        },

        updateMediaDeclination: function(mediaDeclinationData) {
            if (mediaDeclinationData instanceof MediacenterFile) {
                $log.error('mediaDeclinationData must not be an instance of MediacenterFile but a raw object');
                return false;
            }

            // work on a copy of mediaDeclination
            var mediaDeclinationDataPut = {
                name: mediaDeclinationData.name,
                media: mediaDeclinationData.media,
                isMainDeclination: mediaDeclinationData.isMainDeclination,
                mediaDeclinationType: angular.copy(mediaDeclinationData.mediaDeclinationType)
            }

            var fileWaitingForUpload = factory.addWaitingUploadFile(mediaDeclinationData.name, mediaDeclinationData.file, null, mediaDeclinationData.media);

            var deferred = $q.defer();

            var progressListener = function(deferred) {
                return function(event) {
                    deferred.notify({
                        'updateType': 'progress',
                        'loaded': event.loaded,
                        'total': event.total
                    });
                    $log.log('progress : ',Math.round(event.loaded * 100 / event.total));
                };
            };

            mediaDeclinationDataPut = ObjectExtra.deleteUndefinedProperties(mediaDeclinationDataPut);

            // convert datas into a FormData object
            var mediaDeclinationFormData = formDataObject({
                'media_declination': mediaDeclinationDataPut,
                '_method': 'PUT'
            });

            $.ajax({
                type: 'POST',
                url: Routing.generate(factory.urlPrefix+'put_mediadeclination',{ id: mediaDeclinationData.id }),
                data: mediaDeclinationFormData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(responseData, textStatus, jqXHR) {
                    deferred.resolve({data: responseData});
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    deferred.reject({data: jqXHR.responseJSON});
                },
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                       xhr.upload.addEventListener('progress', progressListener(deferred), false);
                    } else {
                        $log.warn('Upload progress is not supported.');
                    }
                    return xhr;
                }
            });

            deferred.promise.then(function (response) {
                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();

                // when data fetched, return it to populate the promise
                return response;
            })
            .finally(function() {
                media.waitingForUpdate = false;
                // remove file name from upload queue
                if (null != fileWaitingForUpload) factory.removeWaitingUploadFile(fileWaitingForUpload);
            });

            return deferred.promise;
        },

        deleteFolder: function(folder) {
            folder.waitingForUpdate = true;

            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_folder',{ id: folder.id })).then(function (response) {
                // update subfolders of parent folder
                if (folder.parentFile) folder.parentFile.subfolders.splice(folder.parentFile.subfolders.indexOf(folder), 1);
                // unlink folder from index and let Garbage Collector destroy it
                delete factory.foldersIndex[folder.id];
            })
            .finally(function() {
                if (factory.foldersIndex[folder.id]) factory.foldersIndex[folder.id].waitingForUpdate = false;
            });

            return promise;
        },

        deleteMedia: function(media) {
            media.waitingForUpdate = true;

            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_media',{ id: media.id })).then(function (response) {
                // update list of medias in parent file
                if (media.parentFile) media.parentFile.medias.splice(media.parentFile.medias.indexOf(media), 1);
                // unlink media from index and let Garbage Collector destroy it
                delete factory.mediasIndex[media.id];

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();
            })
            .finally(function() {
                media.waitingForUpdate = false;
            });

            return promise;
        },

        deleteMediaDeclination: function(mediaDeclination) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_mediadeclination',{ id: mediaDeclination.id })).then(function (response) {

                // refresh quota
                BackofficeDiskQuotaFactory.updateInfos();
            });

            return promise;
        },

        trashFolder: function(folder) {
            folder.waitingForUpdate = true;

            var folderPatch = {
                trashed: true
            }

            // there is no shortcut method for patch
            //promise = $http.patch(Routing.generate(factory.urlPrefix+'patch_folder',{ id: folder.id }), {folder: folderPatch});

            var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix+'patch_folder',{ id: folder.id }),
                data: { folder: folderPatch }
            })
            .then(function (response) {
                // update list of folders in parent file
                if (folder.parentFile) folder.parentFile.subfolders.splice(folder.parentFile.subfolders.indexOf(folder), 1);
                // unlink folder from index and let Garbage Collector destroy it
                delete factory.foldersIndex[folder.id];
            })
            .finally(function() {
                folder.waitingForUpdate = false;
            });

            return promise;
        },

        trashMedia: function(media) {
            media.waitingForUpdate = true;

            var mediaPatch = {
                trashed: true
            }

            // there is no shortcut method for patch
            //promise = $http.patch(Routing.generate(factory.urlPrefix+'patch_media',{ id: media.id }), {media: mediaPatch});

            var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix+'patch_media',{ id: media.id }),
                data: { media: mediaPatch }
            })
            .then(function (response) {
                // update list of medias in parent file
                if (media.parentFile) media.parentFile.medias.splice(media.parentFile.medias.indexOf(media), 1);
                // unlink media from index and let Garbage Collector destroy it
                delete factory.mediasIndex[media.id];
            })
            .finally(function() {
                media.waitingForUpdate = false;
            });

            return promise;
        },

        untrashFolder: function(folder) {
            var folderPatch = {
                trashed: 0
            }

            // there is no shortcut method for patch
            // promise = $http.patch(Routing.generate(factory.urlPrefix+'patch_folder',{ id: folder.id }), {folder: folderPatch});

             var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix+'patch_folder',{ id: folder.id }),
                data: { folder: folderPatch }
            })
            .then(function (response) {
                //var folder = response.data.folder;

                // trigger a cache refresh for the folder structure (because some may be have been recreated by the server)
                // this could be handled by parseFile
                factory.refreshCache();

                //folder = factory.parseFile(folder);

                // update response
                //response.data.folder = folder;
                //when data fetched, return it to populate the promise
                return response;
            });

            return promise;
        },

        untrashMedia: function(media) {
            var mediaPatch = {
                trashed: 0
            }

            // there is no shortcut method for patch
            //promise = $http.patch(Routing.generate(factory.urlPrefix+'patch_media',{ id: media.id }), {media: mediaPatch});

             var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix+'patch_media',{ id: media.id }),
                data: { media: mediaPatch }
            })
            .then(function (response) {
                //var media = response.data.media;
                //var folder = null;

                //TODO : handle error
                //if (!media.folder.id) $log.error("media folder id should be defined");

                // trigger a cache refresh for the folder structure (because some may be have been recreated by the server)
                // this could be handled by parseFile
                factory.refreshCache();

                /*
                //retrieve folder object from the js structure
                folder = factory.foldersIndex[media.folder.id];

                //parse media (add file object methods)
                media = factory.parseFile(media,folder);

                //add the new media to medias in parent file object
                if (folder) folder.medias.push(media);
                */

                return response;
            });

            return promise;
        },

        findFileFromPath: function(filePath) {
            var deferred = $q.defer();

            if (null == filePath) {
                $log.warn('called findFileFromPath with a null filePath');
                return false;
            }

            // remove ending slash
            var filePath = filePath.replace(/\/$/, '');
            var fileAddress = filePath.split('/');

            // search in cache
            var file = factory.findFileFromPathArray(fileAddress);

            if (file) {

                if ('folder' == file.fileType) {
                    // fetch folder's media if not in cache
                    if (0 == file.medias.length) {
                        factory.getFolder(file.id, 'all').then(function(response) {
                            // resolve main promise
                            deferred.resolve({data: {file: file}});
                        }, function(response) {
                            deferred.reject(response.data);
                        })
                    }
                    // if folder's media already in cache
                    else {
                        // resolve main promise
                        deferred.resolve({data: {file: file}});
                    }
                }

                // fetch compelete media object if necesseray
                if ('media' == file.fileType) {
                    if (false == file.isCompleteObject) {
                        // fetch complete version of the media
                        factory.getMedia(file.id, 'all').then(function (response) {
                            // resolve main promise
                            deferred.resolve({data: {file: file}});
                        }, function(response) {
                            deferred.reject(response.data);
                        });
                    }
                    // if complete media already in cache
                    else {
                        // resolve main promise
                        deferred.resolve({data: {file: file}});
                    }
                }

            }

            // if file not found in cache
            else {
                // all folders are in cache, so we are searching for a media

                // find media's folder
                var folderAddress = filePath.split('/');
                folderAddress.pop();

                // search media's folder in cache
                var folder = factory.findFileFromPathArray(folderAddress);

                if (folder) {
                    // fetch folder's media
                    factory.getFolder(folder.id, 'all').then(function (response) {
                        // retry finding media in updated cache
                        var file = factory.findFileFromPathArray(fileAddress);

                        if (file) {
                            // fetch complete version of the media
                            factory.getMedia(file.id, 'all').then(function (response) {
                                // resolve main promise
                                deferred.resolve({data: {file: file}});
                            }, function(response) {
                                deferred.reject(response.data);
                            });
                        }
                        else {
                            deferred.reject({
                                data: {
                                    message: "file not found"
                                }
                            });
                        }
                    });
                }
            }

            return deferred.promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        refreshCache: function() {
            return factory.refreshCache();
        },

        getMediaDeclination: function(id, locale) {
            if (null == locale) locale = $rootScope.locale;

            return $http.get(Routing.generate(factory.urlPrefix+'get_mediadeclination', {id: id})+'?locale='+locale);
        },

        setLastOpenedFolder: function(folder) {
            if ('folder' != folder.fileType) {
                throw 'cannot set last opened folder on a non-folder object';
            }
            factory.lastOpenedFolder = folder;
        },

        getFilePublications: factory.getFilePublications,
    }

    /*** end public functions ***/
}]);
