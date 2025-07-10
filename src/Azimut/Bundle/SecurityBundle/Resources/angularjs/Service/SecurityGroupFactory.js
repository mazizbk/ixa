/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-12 14:32:25
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityGroupFactory', [
'$log', '$http', '$q', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $q, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('SecurityGroupFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_security_api_';

    factory.isGrantedUser = false;

    //groups contain the whole group list
    factory.groups = [];

    //index array for retrieving a group by id from groups object
    factory.groupsIndex = [];


    /*** privates functions ***/

    factory.getGroupsFromServer = function() {

        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_groups')).then(function (response) {

            // clear groups array object
            factory.groups.splice(0);

            //reset index
            factory.groupsIndex = [];

            for (var i=0; i<response.data.groups.length; i++) {
                factory.groups[i] = response.data.groups[i];
                // update index
                factory.groupsIndex[factory.groups[i].id] = factory.groups[i];
            }

            return factory.groups;
        });

        return promise;
    }

    factory.refreshCache = function() {
        return factory.getGroupsFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    }

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || -1 == $state.current.name.indexOf('.security.') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh for groups');

        return factory.refreshCache();
    }

    /*** end privates functions ***/


    /*** public functions ***/

    return {

        init: function() {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if(factory.initialized) {
                factory.refreshCache();
                deferred.resolve();
            }
            else {
                factory.refreshCache().then(function(response) {
                    factory.isGrantedUser = true;

                    factory.initialized = true;

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
            }

            return deferred.promise;
        },

        groups: function(){
            return factory.groups;
        },

        findGroup: function(id) {
            return factory.groupsIndex[id];
        },

        getGroup: function(id) {

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_group', {id: id})).then(function (response) {
                //var group = response.data.group;

                return response.data;
            });

            return promise;
        },

        createGroup: function(group) {

            var promise = $http.post(Routing.generate(factory.urlPrefix+'post_groups'), {group: group}).then(function (response) {

                var group = response.data.group;

                //add to group list
                factory.groups.push(group);

                // update index
                factory.groupsIndex[group.id] = group;

                return response.data;
            });

            return promise;
        },

        updateGroup: function(group) {

            //work on a copy of group
            var groupPut = {
                name: group.name
            };

            return $http.put(Routing.generate(factory.urlPrefix+'put_group',{ id: group.id }), {group: groupPut}).then(function (response) {

                var group = response.data.group;

                factory.groupsIndex[group.id].name = group.name;

                return group;
            });
        },

        deleteGroup: function(group) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_group',{ id: group.id })).then(function (response) {
                //unlink file and let Garbage Collector destroy it
                factory.groups.splice(factory.groups.indexOf(group),1);
                delete factory.groupsIndex[group.id];
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        }

    }
    /*** public functions ***/

}]);
