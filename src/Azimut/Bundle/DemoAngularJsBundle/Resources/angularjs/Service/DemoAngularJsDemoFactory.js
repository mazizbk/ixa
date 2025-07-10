/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:18:06
 */

'use strict';

angular.module('azimutDemoAngularJs.service')

/**
 * Example of an entity factory structure (plugged to an API)
 *
 * This simple example do not add objects to indexes when fetching them one by
 * one (like for getFile).
 * It don't use typed objects with specific methods.
 *
 * For a more complex example with full indexation and typed objects,
 * see Mediacenter application
 */
.factory('DemoAngularJsDemoFactory', [
'$log', '$http', '$rootScope', '$q', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $rootScope, $q, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('DemoAngularJsDemoFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_demoangularjs_api_';

    factory.isGrantedUser = false;

    factory.files = [];
    factory.filesIndex = [];

    /*** privates functions ***/

    factory.getFilesFromServer = function () {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_files')).then(function (response) {
            // clear files array object
            factory.files.splice(0);

            // reset index
            factory.filesIndex = [];

            for (var i=0; i<response.data.files.length; i++) {
                factory.files[i] = response.data.files[i];
                // update index
                factory.filesIndex[factory.files[i].id] = factory.files[i];
            }

            return factory.files;
        });

        return promise;
    }

    factory.refreshCache = function() {
        return factory.getFilesFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    }

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || -1 == $state.current.name.indexOf('.demoangularjs.') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh');

        return factory.refreshCache();
    }

    /*** end privates functions ***/


    /*** public functions ***/

    return {
        init: function() {
            var deferred = $q.defer();

            $log.log("Factory init");

            factory.refreshCache().then(function(response) {
                factory.isGrantedUser = true;

                // schedule auto cache refresh
                $interval.cancel(refreshIntervalPromise);
                refreshIntervalPromise = $interval(factory.autoRefreshCache, factory.autoCacheRefreshDelay*60*1000);

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

            return deferred.promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        files: function() {
            return factory.files
        },

        getFile: function(id) {
            /*
            var promise = $http.get(Routing.generate(urlPrefix+'get_file', {id: id})).then(function (response) {
                var file = response.data.file;

                return response;
            });

            return promise;*/
        },

        createFile: function() {
        },

        updateFile: function() {
        },


        deleteFile: function() {
        }
    }

    /*** end public functions ***/
}]);
