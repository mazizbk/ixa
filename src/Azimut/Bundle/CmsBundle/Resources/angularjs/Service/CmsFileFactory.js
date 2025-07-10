/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:09:44
 */

'use strict';

angular.module('azimutCms.service')

.factory('CmsFileFactory', [
'$log', '$http', '$rootScope', 'formDataObject', 'ArrayExtra', '$q', 'CmsFile', 'ObjectExtra', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $rootScope, formDataObject, ArrayExtra, $q, CmsFile, ObjectExtra, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('CmsFileFactory');

    var factory = this;
    factory.refreshIntervalPromise = null;

    factory.initialized = false;
    factory.initializedNamespace = null;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_cms_api_';

    factory.isGrantedUser = false;

    //files contain the whole file list
    factory.files = [];

    //index array for retrieving a folder by id from files object
    factory.filesIndex = [];

    factory.availableFileTypes = null;

    factory.waitingCmsFilesBufferCount = null;


    /*** privates functions ***/

    factory.getAvailableTypesFromServer = function(namespace) {
        var routeParams = null;

        if (undefined != namespace) {
            routeParams = {
                namespace: namespace
            }
        }

        var promise = $http.get(Routing.generate(factory.urlPrefix + 'get_cmsfiles_availabletypes', routeParams)).then(function (response) {
            var fileTypes = response.data.types;

            factory.availableFileTypes = fileTypes;
        });
        return promise;
    };

    factory.getFilesFromServer = function(namespace) {
        var routeParams = {
            'locale': 'all',
        };

        if (undefined != namespace) {
            routeParams.namespace = namespace;
        }

        var promise = $http.get(Routing.generate(factory.urlPrefix + 'get_cmsfiles', routeParams)).then(function (response) {
            // clear files array object
            factory.files.splice(0);

            // reset index
            factory.filesIndex = [];

            for (var i = 0; i < response.data.cmsFiles.length; i++) {
                factory.files[i] = new CmsFile(response.data.cmsFiles[i]);
                // update index
                factory.filesIndex[factory.files[i].id] = factory.files[i];
            }
            factory.waitingCmsFilesBufferCount = response.data.waitingCmsFilesBufferCount;

            return factory.files;
        });

        return promise;
    };

    factory.refreshCache = function(namespace) {
        return factory.getFilesFromServer(namespace).then(function(response) {
            factory.refreshDate = new Date();
        });
    };

    factory.autoRefreshCache = function(namespace) {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if (ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || 0 != $state.current.name.indexOf('backoffice.cms') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh', namespace);

        return factory.refreshCache(namespace);
    };

    /*** end privates functions ***/


    /*** public functions ***/

    return {
        // init service (constructor)
        init: function(namespace) {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if (factory.initialized && namespace == factory.initializedNamespace) {
                factory.getAvailableTypesFromServer(namespace);
                factory.refreshCache(namespace);
                deferred.resolve();
            }
            else {
                factory.getAvailableTypesFromServer(namespace).then(function(response) {
                    factory.refreshCache(namespace).then(function(response) {
                        factory.isGrantedUser = true;

                        factory.initialized = true;
                        factory.initializedNamespace = null;

                        // schedule auto cache refresh
                        $interval.cancel(factory.refreshIntervalPromise);
                        factory.refreshIntervalPromise = $interval(function() {
                            factory.autoRefreshCache(namespace);
                        }, factory.autoCacheRefreshDelay*60*1000);

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

        refreshCache: function() {
            factory.refreshCache();
        },

        files: function(type){
            return factory.files
        },

        availableFileTypes:  function() {
            return factory.availableFileTypes;
        },

        getTrashedFiles: function() {
            var routeParams = {
                'locale': 'all',
            };
            return $http.get(Routing.generate(factory.urlPrefix + 'get_trashbin', routeParams)).then(function(response) {
                for(var i=0;i<response.data.cmsFiles.length;i++) {
                    response.data.cmsFiles[i] = new CmsFile(response.data.cmsFiles[i]);
                }
                return response;
            });
        },

        deleteTrashedFiles: function() {
            return $http.delete(Routing.generate(factory.urlPrefix + 'delete_trashbin'));
        },

        createFile: function(cmsFile) {
            var promise = $http.post(Routing.generate(factory.urlPrefix + 'post_cmsfiles'), {cms_file: cmsFile}).then(function (response) {

                var cmsFile = new CmsFile(response.data.cmsFile);
                response.data.cmsFile = cmsFile;

                // Add to file list
                factory.files.push(cmsFile);

                // Update index
                factory.filesIndex[cmsFile.id] = cmsFile;

                return response;
            });

            return promise;
        },

        findFile: function(id) {
            return factory.filesIndex[id];
        },

        getFile: function(id, locale) {
            if (null == locale) locale = $rootScope.locale;

            var promise = $http.get(Routing.generate(factory.urlPrefix + 'get_cmsfile', {id: id})+'?locale='+locale).then(function (response) {
                var cmsFile = new CmsFile(response.data.cmsFile);
                response.data.cmsFile = cmsFile;

                cmsFile.isCompleteObject = true;

                // rename each cmsFileDeclinationId on attachments to cmsFileDeclination
                /*angular.forEach(cmsFile.attachments, function(attachment, key) {
                    cmsFile.attachments[key].cmsFileDeclination = cmsFile.attachments[key].cmsFileDeclinationId;
                    delete cmsFile.attachments[key].cmsFileDeclinationId;
                });*/

                if (undefined != factory.filesIndex[cmsFile.id]) {
                    // Update file index
                    factory.filesIndex[cmsFile.id].name = cmsFile.name;
                }

                return response;
            });

            return promise;
        },

        updateFile: function(cmsFile) {
            //work on a copy of cmsFile
            var cmsFilePut = {
                mainAttachment: cmsFile.mainAttachment,
                secondaryAttachments: cmsFile.secondaryAttachments,
                complementaryAttachment1: cmsFile.complementaryAttachment1,
                complementaryAttachment2: cmsFile.complementaryAttachment2,
                complementaryAttachment3: cmsFile.complementaryAttachment3,
                complementaryAttachment4: cmsFile.complementaryAttachment4,
                relatedArticles: cmsFile.relatedArticles,
                autoMetas: cmsFile.autoMetas,
                metaTitle: cmsFile.metaTitle,
                metaDescription: cmsFile.metaDescription,
                cmsFileType: ObjectExtra.deepCopy(cmsFile.cmsFileType),
            }
            delete cmsFilePut.supportsComments;

            var promise = $http.put(Routing.generate(factory.urlPrefix + 'put_cmsfile',{ id: cmsFile.id }), {cms_file: cmsFilePut}).then(function (response) {
                var cmsFile = new CmsFile(response.data.cmsFile);
                response.data.cmsFile = cmsFile;

                if (undefined != factory.filesIndex[cmsFile.id]) {
                    //update file index
                    factory.filesIndex[cmsFile.id].name = cmsFile.name;
                    factory.filesIndex[cmsFile.id].isVisible = cmsFile.isVisible;
                }

                return response;
            });

            return promise;
        },

        deleteFile: function(cmsFile) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix + 'delete_cmsfile',{ id: cmsFile.id })).then(function (response) {
                //unlink file and let Garbage Collector destroy it
                factory.files.splice(factory.files.indexOf(cmsFile),1);
                delete factory.filesIndex[cmsFile.id];
            });

            return promise;
        },

        trashFile: function(cmsFile) {
            factory.filesIndex[cmsFile.id].waitingForUpdate = true;

            var cmsFilePatch = {
                trashed: true
            }

            //there is no shortcut method for patch
            //promise = $http.patch(Routing.generate(factory.urlPrefix + 'patch_cmsFile',{ id: cmsFile.id }), {cmsFile: cmsFilePatch});

            var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix + 'patch_cmsfile',{ id: cmsFile.id }),
                data: { cms_file: cmsFilePatch }
            })
            .then(function (response) {
                factory.files.splice(factory.files.indexOf(cmsFile), 1);
                //unlink cmsFile and let Garbage Collector destroy it
                delete factory.filesIndex[cmsFile.id];
            })
            .finally(function() {
                if (factory.filesIndex[cmsFile.id]) factory.filesIndex[cmsFile.id].waitingForUpdate = false;
            });

            return promise;
        },

        untrashFile: function(cmsFile) {
            var cmsFilePatch = {
                trashed: 0
            }

            //there is no shortcut method for patch
            //promise = $http.patch(Routing.generate(factory.urlPrefix + 'patch_cmsFile',{ id: cmsFile.id }), {cmsFile: cmsFilePatch});

             var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix + 'patch_cmsfile',{ id: cmsFile.id }),
                data: { cms_file: cmsFilePatch }
            }).then(function(response) {
                var cmsFile = new CmsFile(response.data.cmsFile);
                response.data.cmsFile = cmsFile;

                //add to file list
                factory.files.push(cmsFile);

                // update index
                factory.filesIndex[cmsFile.id] = cmsFile;
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        waitingCmsFilesBufferCount: function() {
            return factory.waitingCmsFilesBufferCount;
        },

        getFilePublications: function(id) {
            return $http.get(Routing.generate(factory.urlPrefix + 'get_cmsfile_publications', {id: id}));
        },
    }

    /*** end public functions ***/
}]);
