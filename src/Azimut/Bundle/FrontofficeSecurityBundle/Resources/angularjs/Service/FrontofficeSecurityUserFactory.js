/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 12:31:43
 */

'use strict';

angular.module('azimutFrontofficeSecurity.service')

.factory('FrontofficeSecurityUserFactory', [
'$log', '$http', '$q', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $q, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('FrontofficeSecurityUserFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_frontofficesecurity_api_';

    factory.isGrantedUser = false;

    factory.users = [];
    factory.usersIndex = [];


    /*** privates functions ***/

    factory.getUsersFromServer = function() {

        return $http.get(Routing.generate(factory.urlPrefix+'get_users')).then(function (response) {

            // clear data
            factory.users.splice(0);
            factory.usersIndex = [];

            for (var i=0; i<response.data.users.length; i++) {
                factory.users.push(response.data.users[i]);
                // update index
                factory.usersIndex[response.data.users[i].id] = response.data.users[i];
            }

            // return factory.users;
        });
    };

    factory.refreshCache = function() {
        return factory.getUsersFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    }

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || -1 == $state.current.name.indexOf('.security.') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh for frontoffice users');

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

        users: function() {
            return factory.users;
        },

        findUser: function(id) {
            return factory.usersIndex[id];
        },

        getUser: function(id) {
            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_user', {id: id}));

            return promise;
        },

        createUser: function(user) {
            // work on a copy of user
            var userData = {
                email: user.email,
                plainPassword: user.plainPassword,
                firstName: user.firstName,
                lastName: user.lastName,
                roles: user.roles,
                isActive: user.isActive,
            };

            return $http.post(Routing.generate(factory.urlPrefix+'post_users'), {frontoffice_user: userData}).then(function (response) {
                var user = response.data.user;
                factory.users.push(user);
                factory.usersIndex[user.id] = user;
                // trigger cache refresh
                factory.refreshCache();

                return response.data;
            });
        },

        updateUser: function(user) {
            // work on a copy of user
            var userData = {
                email: user.email,
                plainPassword: user.plainPassword,
                firstName: user.firstName,
                lastName: user.lastName,
                roles: user.roles,
                isActive: user.isActive,
            };

            return $http.put(Routing.generate(factory.urlPrefix+'put_user', {id: user.id}), {frontoffice_user: userData}).then(function (response) {
                var user = response.data.user;

                factory.usersIndex[user.id].email = user.email;
                factory.usersIndex[user.id].firstName = user.firstName;
                factory.usersIndex[user.id].lastName = user.lastName;
                factory.usersIndex[user.id].roles = user.roles;
                factory.usersIndex[user.id].isActive = user.isActive;

                return user;
            });
        },

        deleteUser: function(user) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_user',{ id: user.id })).then(function (response) {
                // unlink user and let Garbage Collector destroy it
                factory.users.splice(factory.users.indexOf(user),1);
                delete factory.usersIndex[user.id];
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        impersonateUser: function(user) {
            return $http.post(Routing.generate(factory.urlPrefix+'post_impersonate',{ id: user.id }));
        },
    };

    /*** end public functions ***/

}]);
