/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-09-23 15:35:16
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityAccessRightFactory', [
'$log','$http', '$rootScope', '$q', 'ObjectExtra', 'ArrayExtra', 'SecurityAccessRightApp', 'SecurityAccessRightClass', 'SecurityAccessRightRoles', 'SecurityAccessRightObject',
function($log, $http, $rootScope, $q, ObjectExtra, ArrayExtra, SecurityAccessRightApp, SecurityAccessRightClass, SecurityAccessRightRoles, SecurityAccessRightObject) {
    var factory = this;

    factory.urlPrefix = 'azimut_security_api_';

    factory.isGrantedUser = false;

    factory.initialized = false;

    //accessRights contain the whole accessRight list
    factory.accessRights = [];

    //index array for retrieving an accessRight by id from accessRights object
    factory.accessRightsIndex = [];

    //factory.availableAccessRightTypes = null;

    factory.simpleRoles = []; //roles different from role app or super admin

    factory.appRoles = [];

    factory.classes = {};

    factory.availableObjectRoles = null;

    /*factory.getAvailableTypesFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_accessrights_availabletypes ')).then(function (response) {
            var accessRight = response.data.types;

            factory.availableAccessRightTypes = accessRightTypes;
        });
        return promise;
    }*/

    factory.getAccessRightsFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_accessrights')).then(function (response) {

            for (var i = 0; i < factory.accessRights.length; i++) delete factory.accessRights[i];

            factory.accessRights = response.data.accessRights;
            factory.accessRightsIndex = [];

            //modify received datas
            for (var i = 0; i < factory.accessRights.length; i++) {
                factory.accessRightsIndex[factory.accessRights[i].id] = factory.accessRights[i];
            }

            return factory.accessRights;
        });
            return promise;
    }

    factory.getRolesFromServer = function () {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_roles', {showAppClasses: 'true'})).then(function(response){
            var roles = response.data.roles;
            factory.appRoles = [];
            factory.simpleRoles = [];
            factory.classes = {};

            for (var i = 0; i < roles.length; i++){
                //get all the appl role which contains roles on classes
                if (roles[i].role.includes('APP')){

                    if (undefined != roles[i].classes) {

                        var classes = {};

                        for (var key in roles[i].classes) {
                            // transform backslashes into slashes in class namespaces
                            //roles[i].classes[key].namespace = roles[i].classes[key].namespace;
                            // transform index of classes from shortname to full namespace
                            classes[roles[i].classes[key].namespace] = roles[i].classes[key];
                        }

                        angular.extend(factory.classes, classes);

                    }

                    factory.appRoles.push(roles[i]);

                }
                else if (!roles[i].role.includes('SUPER_ADMIN') && roles[i].role.includes('GLOBAL_')){
                    factory.simpleRoles.push(roles[i]);
                }
            }

            return roles;
        });

        return promise;
    }


    factory.formatAccessRightForApi = function(originalAccessRights) {
        //work on a copy of accessRight
        var accessRights = ObjectExtra.deepCopy(originalAccessRights);
        var outputAccessRights =[];
        // sort out accessrights according to their type
        for(var right in accessRights){

            if(undefined != accessRights[right]){

                switch(right){

                    case 'apps':
                        //structure accessright application according to form type
                        var accessRightApp = {
                            type : 'app_roles',
                            accessRightType : {
                                roles :[]
                            }
                        }
                        if(accessRights[right].length != 0){
                            //for each role keep just the id
                            for(var j=0; j<accessRights[right].length; j++)
                            {
                                accessRightApp.accessRightType.roles.push(accessRights.apps[j].id);
                            }
                            //push new formatted accessrightapp
                            outputAccessRights.push(accessRightApp);
                        }

                        break;

                    case 'classes':
                        //for each access right class keep classname and role id
                        for(var className in accessRights[right])
                        {
                            var accessRightClass = accessRights[right][className].toRawData();

                            if( accessRightClass.accessRightType.roles.length != 0)
                            {
                                delete accessRightClass.id;
                                delete accessRightClass.userId;
                                delete accessRightClass.groupId;
                                accessRightClass.accessRightType.class = accessRightClass.accessRightType.class.replace(/\//g, '\\');
                                for(var j=0; j<accessRightClass.accessRightType.roles.length; j++)
                                {
                                    delete accessRightClass.accessRightType.roles[j].role;
                                    accessRightClass.accessRightType.roles[j] = accessRightClass.accessRightType.roles[j].id;
                                    delete accessRightClass.accessRightType.roles[j].id;
                                }
                                 //push new formatted accessrightclass
                                outputAccessRights.push(accessRightClass);
                            }
                        }
                        break;

                    case 'roles':
                        //for each access right class keep classname and role id
                        var accessRightRoles = {
                            type : 'roles',
                            accessRightType : {
                                roles :[]
                            }
                        }

                        if(accessRights[right].roles.length != 0){
                            //for each role keep just the id
                            for(var role in accessRights[right].roles)
                            {
                                accessRightRoles.accessRightType.roles.push(accessRights[right].roles[role].id);
                            }
                            //push new formatted accessrightroles
                            outputAccessRights.push(accessRightRoles);
                        }
                        break;

                    default :
                        //for each object access right keep objectId, type of object, and role Id
                        for(var object in accessRights[right])
                        {
                            var accessRightObject = accessRights[right][object].toRawData();
                            delete accessRightObject.id;
                            delete accessRightObject.userId;
                            delete accessRightObject.groupId;
                            if(accessRightObject.accessRightType.roles.length != 0){
                                for(var j=0; j<accessRightObject.accessRightType.roles.length; j++)
                                {
                                    accessRightObject.accessRightType.roles[j] = accessRightObject.accessRightType.roles[j].id;
                                    delete accessRightObject.accessRightType.roles[j].id;
                                }
                                //push new formatted accessrightclass
                                outputAccessRights.push(accessRightObject);
                            }
                        }
                }
            }
        }
        return outputAccessRights;
    }

    factory.formatAccessRightsFromApi = function(accessRights) {
      //format accessrights received from api in the right structure
        // for the view
        var securityAccessRightApps = new SecurityAccessRightApp();
        var securityAccessRightsClass = [];
        var securityAccessRightRoles = new SecurityAccessRightRoles();
        var securityAccessRightObject = [];

        for (var i = accessRights.length - 1; i >= 0; i--) {
            switch (accessRights[i].accessRightType){
                case 'app_roles':
                    securityAccessRightApps = new SecurityAccessRightApp(accessRights[i]);
                    break;
                case 'class':
                    securityAccessRightsClass.push(new SecurityAccessRightClass(accessRights[i]));
                    break;
                case'roles':
                    securityAccessRightRoles  = new SecurityAccessRightRoles(accessRights[i]);
                    break;
                default:
                    securityAccessRightObject.push(new SecurityAccessRightObject(accessRights[i]));
            }
        }

        return {
            apps: securityAccessRightApps,
            classes: securityAccessRightsClass,
            roles: securityAccessRightRoles,
            object: securityAccessRightObject
        };
    };

    /*** end private functions ***/

    return {
        /*** public functions ***/

        init: function() {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if(factory.initialized) {
                factory.getRolesFromServer();
                deferred.resolve();
            }
            else {
                factory.getRolesFromServer().then(function(response) {
                    factory.isGrantedUser = true;
                    factory.initialized = true;
                    deferred.resolve();
                }, function(response) {
                    $log.error('getRolesFromServer ERROR');

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

        accessRights: function(type) {
            return factory.accessRights;
        },

        index: function () {
            return factory.getAccessRightsFromServer();
        },

        availableAccessRightTypes:  function() {
            return factory.availableAccessRightTypes;
        },

      /*  availableApplicationRoles: function() {
            return factory.availableApplicationRoles;
        },  */

        getAppRoles: function() {
            return factory.appRoles;
        },

        getSimpleRoles: function(){
            return factory.simpleRoles;
        },

        getClasses: function() {
            return factory.classes;
        },

        getClassHeaderRoles: function(classToLookFor){
            //get roles of the class from applicationRoles
            var headerRoles = [];
            for(var i = 0; i < factory.appRoles.length; i++){

                if(factory.appRoles[i].classes[classToLookFor] != undefined){
                    headerRoles = ArrayExtra.merge(headerRoles, factory.appRoles[i].classes[classToLookFor]['roles']['role']);

                    for(var property in factory.appRoles[i].classes ){
                        if(factory.appRoles[i].classes[property]['parentClasses'] == classToLookFor){
                            headerRoles = ArrayExtra.merge(headerRoles, factory.appRoles[i].classes[property]['roles']['role']);
                        }
                    }
                };
            }

            return headerRoles;
        },

        getClassRolesIds: function(classToLookFor){

            var roleIds = [];
            for(var i = 0; i < factory.appRoles.length; i++){

                if(factory.appRoles[i].classes[classToLookFor] != undefined){

                    for(var index=0;index<factory.appRoles[i].classes[classToLookFor]['roles'].length;index++){
                        roleIds.push(factory.appRoles[i].classes[classToLookFor]['roles'][index].id);
                    }
                };
            }
            return roleIds;
        },

        getAccessRight: function(id) {

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_accessright', {id: id})).then(function (response) {
                var accessRight = response.data.accessRight;//??? data.ar ou data.accessRight

                //update accessRight index
                factory.accessRightsIndex[accessRight.id].name = accessRight.name;
                return response.data;
            });

            return promise;
        },

        getUserAccessRights: function(userId, inherited) {
            if(!inherited){
                inherited = 0;
            }
            return $http.get(Routing.generate(factory.urlPrefix+'get_accessrights', {userId: userId, inheritedRights: inherited})).then(function(response) {
                response.data.accessRights = factory.formatAccessRightsFromApi(response.data.accessRights);
                if(inherited) {
                    response.data.inheritedAccessRights = factory.formatAccessRightsFromApi(response.data.inheritedAccessRights);
                }

                return response;
            });
        },

        putUserAccessRights: function(userId, user_access_right) {
            var promise = $http.put(Routing.generate(factory.urlPrefix+'put_useraccessright', {id: userId}), user_access_right).then(function (response) {
                var user = response.data.user;
                return response;
            });

            return promise;
        },

        getGroupAccessRights: function(groupId) {
          //  return $http.get(Routing.generate(factory.urlPrefix+'get_accessrights', {groupId: groupId}));

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_accessrights', {groupId: groupId})).then(function(response) {
                var accessRights = response.data.accessRights;
                response.data.accessRights = factory.formatAccessRightsFromApi(accessRights);

                return response;
            });

            return promise;
        },

        putGroupAccessRights: function(groupId, group_access_right) {
            var promise = $http.put(Routing.generate(factory.urlPrefix+'put_groupaccessright', {id: groupId}), group_access_right).then(function (response) {
                var group = response.data.group;
                return response;
            });

            return promise;
        },

        getObjectOfClass: function(className) {
           // className = className.replace(/\\/g, '_');
            var url = Routing.generate(factory.urlPrefix+'get_objectsofclass', {className:className});

            // route to access api/security/objectsbyclasses/{class}.{_format}
            var promise = $http.get(url).then(function(response) {
              //  var objects = response.data;
                return response;
            });

            return promise;
        },

        toRawData: function(accessRight){
            return factory.formatAccessRightForApi(accessRight);

        },

        deleteAccessRight: function(accessRight) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_accessright',{ id: accessRight.id })).then(function (response) {
                //unlink file and let Garbage Collector destroy it
                factory.accessRight.splice(factory.accessRight.indexOf(accessRight),1);
                delete factory.accessRightIndex[accessRight.id];
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        }



    }

    /*** end public functions ***/

}]);
