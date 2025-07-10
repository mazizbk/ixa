/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:18:06
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityUserFactory', [
'$log', '$http', '$q', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $q, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('SecurityUserFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_security_api_';

    factory.isGrantedUser = false;

    // users contain the whole user list
    factory.users = [];

    // index array for retrieving a user by id from users object
    factory.usersIndex = [];


    /*** privates functions ***/

    factory.getUsersFromServer = function() {

        return $http.get(Routing.generate(factory.urlPrefix+'get_users')).then(function (response) {

            // clear users array object
            factory.users.splice(0);

            //reset index
            factory.usersIndex = [];

            for (var i=0; i<response.data.users.length; i++) {
                factory.users.push(response.data.users[i]);
                response.data.users[i].isSuperAdmin = false;
                response.data.users[i].isConfirmed = !!response.data.users[i].firstName && !!response.data.users[i].lastName;
                // update index
                factory.usersIndex[response.data.users[i].id] = response.data.users[i];
            }

            for (var i=0; i<response.data.super_admin_users.length; i++) {
                factory.users.push(response.data.super_admin_users[i]);
                response.data.super_admin_users[i].isSuperAdmin = true;
                response.data.super_admin_users[i].isConfirmed = !!response.data.super_admin_users[i].firstName && !!response.data.super_admin_users[i].lastName;
                // update index
                factory.usersIndex[response.data.super_admin_users[i].id] = response.data.super_admin_users[i];
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

        $log.info('Trigger auto cache refresh for users');

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

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_user', {id: id})).then(function (response) {
                //var user = response.data.user;

                return response.data;
            });

            return promise;
        },

        createUser: function(user) {

            var groupsPost = [];

            //keep only ids of groups
            if(user.groups) {
                for (var i=0; i<user.groups.length; i++) {
                    groupsPost.push(user.groups[i].id);
                }
            }

            //work on a copy of user
            var userPost = {
                username: user.username,
                groups: groupsPost
            };

            return $http.post(Routing.generate(factory.urlPrefix+'post_users'), {user: userPost}).then(function (response) {

                var user = response.data.user;
                user.isConfirmed = !!user.firstName && !!user.lastName;
                user.isSuperAdmin = false;

                //add to user list
                factory.users.push(user);

                // update index
                factory.usersIndex[user.id] = user;

                // trigger cache refresh
                factory.refreshCache();

                return response.data;
            });
        },

        updateUser: function(user) {

            var groupsPut = [];

            //keep only ids of groups
            for(var i=0;i<user.groups.length;i++) {
                groupsPut.push(user.groups[i].id);
            }

            //work on a copy of user
            var userPut = {
                groups: groupsPut
            };

            return $http.put(Routing.generate(factory.urlPrefix+'put_user',{ id: user.id }), {user: userPut}).then(function (response) {
                var user = response.data.user;
                user.isConfirmed = !!user.firstName && !!user.lastName;

                factory.usersIndex[user.id].username = user.username;
                factory.usersIndex[user.id].firstName = user.firstName;
                factory.usersIndex[user.id].lastName = user.lastName;
                factory.usersIndex[user.id].groups = user.groups;

                return user;
            });
        },

        deleteUser: function(user) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_user',{ id: user.id })).then(function (response) {
                //unlink user and let Garbage Collector destroy it
                factory.users.splice(factory.users.indexOf(user),1);
                delete factory.usersIndex[user.id];
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        findUserByEmailFromLogin: function(email){
            return $http.get(Routing.generate('azimut_security_api_find_user_from_login', {email: email}));
        },

        resendValidationEmail: function(id){
            return $http.post(Routing.generate('azimut_security_api_post_user_validation_email', {user: id}));
        }

    };

    /*** end public functions ***/

}]);
