/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:09:44
 */

'use strict';

angular.module('azimutModeration.service')

.factory('CmsFileBufferFactory', [
'$log', '$http', '$q', '$interval', 'ActivityMonitorService', '$state', 'ObjectExtra',
function($log, $http, $q, $interval, ActivityMonitorService, $state, ObjectExtra) {
    $log = $log.getInstance('CmsFileBufferFactory');

    var factory = this;
    factory.refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_moderation_api_';

    factory.isGrantedUser = false;

    //files contain the whole file list
    factory.files = [];

    //index array for retrieving a folder by id from files object
    factory.filesIndex = [];

    factory.availableFileTypes = null;


    /*** privates functions ***/

    factory.getAvailableTypesFromServer = function() {
        return $http.get(Routing.generate(factory.urlPrefix+'get_cmsfilesbuffer_availabletypes')).then(function (response) {
            var fileTypes = response.data.types;

            factory.availableFileTypes = fileTypes;
        });
    };

    factory.getFilesFromServer = function() {
        return $http.get(Routing.generate(factory.urlPrefix+'get_cmsfilesbuffer')).then(function (response) {

            // clear files array object
            factory.files.splice(0);

            // reset index
            factory.filesIndex = [];

            for (var i=0; i<response.data.cmsFilesBuffer.length; i++) {
                factory.files[i] = response.data.cmsFilesBuffer[i];
                // update index
                factory.filesIndex[factory.files[i].id] = factory.files[i];
            }

            return factory.files;
        });
    };

    factory.refreshCache = function() {
        return factory.getFilesFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    };

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || 0 != $state.current.name.indexOf('backoffice.moderation') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh');

        return factory.refreshCache();
    };

    /*** end privates functions ***/


    /*** public functions ***/

    return {
        // init service (constructor)
        init: function() {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if(factory.initialized) {
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
                        $interval.cancel(factory.refreshIntervalPromise);
                        factory.refreshIntervalPromise = $interval(function() {
                            factory.autoRefreshCache();
                        }, factory.autoCacheRefreshDelay*60*1000);

                        deferred.resolve();
                    }, function(response) {
                        // if api access is forbidden or unauthorized
                        if(401 == response.data.error.code || 403 == response.data.error.code) {
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
                    if(401 == response.data.error.code || 403 == response.data.error.code) {
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

        findFile: function(id) {
            return factory.filesIndex[id];
        },

        getFile: function(id) {
            return $http.get(Routing.generate(factory.urlPrefix+'get_cmsfilebuffer', {id: id})).then(function (response) {
                var cmsFileBuffer = response.data.cmsFileBuffer;

                if(undefined != factory.filesIndex[cmsFileBuffer.id]) {
                    //update file index
                    factory.filesIndex[cmsFileBuffer.id].name = cmsFileBuffer.name;
                }

                return response;
            });
        },

        convertFile: function(cmsFileBuffer) {
            // Work on a copy of cmsFileBuffer
            var cmsFileBufferData = {
                locale: cmsFileBuffer.locale,
                type: cmsFileBuffer.type,
                cmsFileBufferType: ObjectExtra.deepCopy(cmsFileBuffer.cmsFileBufferType)
            }

            return $http.post(Routing.generate(factory.urlPrefix+'post_convertcmsfilebuffer', {id: cmsFileBuffer.id}), {cms_file_buffer: cmsFileBufferData}).then(function (response) {
                //unlink file and let Garbage Collector destroy it
                factory.files.splice(factory.files.indexOf(cmsFileBuffer),1);
                delete factory.filesIndex[cmsFileBuffer.id];

                return response;
            });
        },

        deleteFile: function(cmsFileBuffer) {
            return $http.delete(Routing.generate(factory.urlPrefix+'delete_cmsfilebuffer',{ id: cmsFileBuffer.id })).then(function (response) {
                //unlink file and let Garbage Collector destroy it
                factory.files.splice(factory.files.indexOf(cmsFileBuffer),1);
                delete factory.filesIndex[cmsFileBuffer.id];
            });
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        }
    }

    /*** end public functions ***/
}]);
