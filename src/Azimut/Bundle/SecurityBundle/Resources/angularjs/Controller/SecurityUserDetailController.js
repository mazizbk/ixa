/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-12 11:44:56
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityUserDetailController', [
'$log', '$scope', '$state', '$stateParams', '$q', 'NotificationService','FormsBag', 'SecurityUserFactory', 'SecurityAccessRightFactory', 'SecurityAccessRightClass', 'SecurityAccessRightObject', 'SecurityClassesHierarchy', 'SecurityClassesParent', 'SecurityClassesSecurityType', '$templateCache',
function($log, $scope, $state, $stateParams, $q, NotificationService, FormsBag, SecurityUserFactory, SecurityAccessRightFactory, SecurityAccessRightClass, SecurityAccessRightObject, SecurityClassesHierarchy, SecurityClassesParent, SecurityClassesSecurityType, $templateCache) {
    $log = $log.getInstance('SecurityUserDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.formUserTemplateUrl = Routing.generate('azimut_security_backoffice_jsview_user_update_form');
    $templateCache.remove($scope.formUserTemplateUrl);

    $scope.forms = new FormsBag();

    // Contains promises for initial loading: we need multiple data before the page is fully loaded
    var initialLoadingPromises = [];

    initialLoadingPromises.push(SecurityUserFactory.getUser($stateParams.id).then(function(response) {

        var user = response.user;

        $scope.user = SecurityUserFactory.findUser(user.id);

        $scope.forms.data.user = user;
        $scope.forms.data.user.isConfirmed = !!user.firstName && !!user.lastName;

        $scope.forms.params.user = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.submit();
            },
            cancelAction: function() {
                $state.go('backoffice.security.user_list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('user.has.not.been.saved.are.you.sure.you.want.to.continue')
        };
    }));

    //form values contains affectables values for compounds checkboxes (groups here)
    $scope.forms.values.user = {
        groups: $scope.groups
    };

    $scope.saveUser = function(user) {
        return SecurityUserFactory.updateUser(user).then(function() {
            // remove dirty state on form
            if (undefined != $scope.forms.params.user.formController) {
                $scope.forms.params.user.formController.$setPristine();
            }

            // clear form error messages
            delete $scope.forms.errors.user;
        }, function(response) {
            $log.error('Update user failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.user.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.user = response.data.errors;
            }

            // forward rejection
            return $q.reject(response);
        });
    };

    // Access right

    $scope.appRoles = SecurityAccessRightFactory.getAppRoles(); // all the application roles
    $scope.roles = SecurityAccessRightFactory.getSimpleRoles(); // all the simple roles as create, view, edit
    $scope.userAccessRightAppRoles = []; //var for application roles
    $scope.userAccessRightClass = [];
    $scope.userAccessRightObjectRoles = [];
    $scope.classes = SecurityAccessRightFactory.getClasses();
    $scope.objectsOfClass = [];
    $scope.headersOfClass =[];

    initialLoadingPromises.push(SecurityAccessRightFactory.getUserAccessRights($stateParams.id, 1).then(function(response) {

        //match user access rights with possible access rights
        $scope.forms.data.user_access_right = response.data.accessRights;
        $scope.inheritedAccessRights = response.data.inheritedAccessRights;

        $scope.forms.data.user_access_right.apps = $scope.forms.data.user_access_right.apps.toFormData();

        // transform classes array index to namespace
        var indexedClasses = {};
        for (var j = $scope.forms.data.user_access_right.classes.length - 1; j >= 0; j--) {
            indexedClasses[$scope.forms.data.user_access_right.classes[j].class] = $scope.forms.data.user_access_right.classes[j].toFormData();
        }
        $scope.forms.data.user_access_right.classes = indexedClasses;

        for(var className in $scope.classes) {
            if(undefined == $scope.forms.data.user_access_right.classes[className]) {
                $scope.forms.data.user_access_right.classes[className] = new SecurityAccessRightClass({class: className});
            }
        }

        //creating an indexed array of objects
        var indexedObjects= {};
        for (var i = $scope.forms.data.user_access_right.object.length - 1; i >= 0; i--) {
            //indexed with [type + objId]
            indexedObjects[$scope.forms.data.user_access_right.object[i].type + $scope.forms.data.user_access_right.object[i].objectId] = $scope.forms.data.user_access_right.object[i].toFormData();
        }
        $scope.forms.data.user_access_right.object = indexedObjects;

        return updateInheritedData();
    }));

    $q.all(initialLoadingPromises).then(function(){
        $scope.mainContentLoaded();
        $scope.$watch('forms.data.user_access_right', updateInheritedData, true);
    });


    var updateInheritedData = function updateInheritedData() {
        var promises = [];
        $scope.forms.inheritedData = {
            user_access_right: {
                apps: {},
                classes: {},
                object: {},
                roles: {}
            }
        };

        // Inherited roles
        $scope.forms.inheritedData.user_access_right.roles = $scope.inheritedAccessRights.roles;

        // Inherited apps
        $scope.forms.inheritedData.user_access_right.apps = $scope.inheritedAccessRights.apps.toFormData();

        for(var objI=0;objI<$scope.inheritedAccessRights.object.length;objI++) {
            var objectRight = $scope.inheritedAccessRights.object[objI];
            $scope.forms.inheritedData.user_access_right.object[objectRight.type+objectRight.objectId] = objectRight;
        }

        // This array contains rights that are inherited, but should not be used to compute other inherited rights.
        // For example, if you have WRITE right on an object, you implicitly get a READ right on that object.
        // This READ right gives you a READ right on every parent of that object. However, being able to READ this
        // object's parents does not grant you the READ right on this object siblings: the READ right on the object's
        // parents should not be used to compute other objects rights
        $scope.parentsViewInheritedRights = [];

        // Inherited classes
        var indexedClasses = {};
        for (var j = $scope.inheritedAccessRights.classes.length - 1; j >= 0; j--) {
            indexedClasses[$scope.inheritedAccessRights.classes[j].class] = $scope.inheritedAccessRights.classes[j].toFormData();
        }
        $scope.forms.inheritedData.user_access_right.classes = indexedClasses;

        for(var className in $scope.classes) {
            if(undefined == $scope.forms.inheritedData.user_access_right.classes[className]) {
                // Try by parents
                promises.push(SecurityClassesHierarchy.getClassParents(className).then(
                    (function(className){
                        return function(parents){
                            for(var i in parents) {
                                var parent = parents[i];
                                var roles = [];
                                if(undefined != $scope.forms.inheritedData.user_access_right.classes[parent]) {
                                    roles = angular.copy($scope.forms.inheritedData.user_access_right.classes[parent].roles);
                                }
                                if(undefined != $scope.forms.data.user_access_right.classes[parent]) {
                                    for(var k in $scope.forms.data.user_access_right.classes[parent].roles) {
                                        roles.push(angular.copy($scope.forms.data.user_access_right.classes[parent].roles[k]));
                                    }
                                }
                                $scope.forms.inheritedData.user_access_right.classes[className] = new SecurityAccessRightClass({
                                    'class': className,
                                    roles: roles
                                });
                            }

                            // If class still has no rights, we store an empty SecurityAccessRightClass
                            if(undefined == $scope.forms.inheritedData.user_access_right.classes[className]) {
                                $scope.forms.inheritedData.user_access_right.classes[className] = new SecurityAccessRightClass({class: className});
                            }
                        }
                    })(className)
                ));
            }
        }

        // Inherited object
        return $q.all(promises).then(function(){
            var promises = [], flattenObjectsReverse = [];
            for(className in $scope.objectsOfClass) {
                var objects = $scope.objectsOfClass[className];
                var flattenObjects = [];
                for(var i in objects) {
                    flattenObjects.push(objects[i]);
                    var children = flattenChildren(objects[i]);
                    for(var j in children) {
                        flattenObjects.push(children[j]);
                        flattenObjectsReverse.push(children[j]);
                    }
                    flattenObjectsReverse.push(objects[i]);
                }
                promises.push(updateInheritedDataRecursive(objects, className).then((function(objects, className){
                    return function(){
                        var promises3 = [];
                        for(var i in objects) {
                            var object = objects[i];
                            // This updates inherited roles for _object_ from _parent objects_
                            promises3.push(getObjectParents(object, className).then(
                                (function(object){
                                    return function(parents){
                                        var roles = $scope.forms.inheritedData.user_access_right.object.hasOwnProperty(object.accessRightType+object.id)?$scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id].roles:[];
                                        var promises2 = [];

                                        for(var i in parents) {
                                            var parent = parents[i];
                                            promises2.push(SecurityClassesSecurityType.getSecurityType(parent.class).then(
                                                (function(parent) {
                                                    return function (securityType) {
                                                        var k;
                                                        if (undefined != $scope.forms.inheritedData.user_access_right.object[securityType+parent.id]) {
                                                            for (k in $scope.forms.inheritedData.user_access_right.object[securityType+parent.id].roles) {
                                                                roles.push(angular.copy($scope.forms.inheritedData.user_access_right.object[securityType + parent.id].roles[k]));
                                                            }
                                                        }
                                                        if (undefined != $scope.forms.data.user_access_right.object[securityType+parent.id]) {
                                                            for (k in $scope.forms.data.user_access_right.object[securityType+parent.id].roles) {
                                                                roles.push(angular.copy($scope.forms.data.user_access_right.object[securityType+parent.id].roles[k]));
                                                            }
                                                        }
                                                    }
                                                })(parent)
                                            ));
                                        }

                                        return $q.all(promises2).then(function(){
                                            $scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id] = new SecurityAccessRightObject({
                                                accessRightType: object.accessRightType,
                                                objectId: object.id,
                                                roles: roles
                                            });
                                        });
                                    };
                                })(object)
                            ));
                        }

                        return $q.all(promises3);
                    };
                })(flattenObjects, className)));
            }

            return $q.all(promises).then((function(objects) {
                return function() {
                    var promises = [];
                    for(var j=0;j<objects.length;j++) {
                        var object = objects[j];
                        promises.push(SecurityClassesParent.getParent(object.class).then((function(object){
                            return function(parentClass){
                                var classRoles = $scope.classes[parentClass].roles;
                                var readRole;
                                for(var i=0;i<classRoles.length;i++) {
                                    var role = classRoles[i];
                                    if(role.role.indexOf('READ')>-1 || role.role.indexOf('VIEW')>-1) {
                                        readRole = role;
                                        break;
                                    }
                                }
                                if(!readRole) {
                                    $log.warn('Unable to find VIEW/READ role for class '+parentClass+' (from '+object.class+'#'+object.id+')');
                                    return;
                                }

                                var accessRight = $scope.forms.inheritedData.user_access_right.object.hasOwnProperty(object.accessRightType+object.id)?
                                        $scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id]:
                                        new SecurityAccessRightObject({
                                            accessRightType: object.accessRightType,
                                            objectId: object.id,
                                            roles: []
                                        })
                                    ;
                                var roles = accessRight.roles;
                                // If object has no role, we check if we can READ it through one of its children
                                if(roles.length == 0) {
                                    var children = flattenChildren(object);
                                    for(var j=0; j<children.length;j++) {
                                        var child = children[j];
                                        var childrenRights = $scope.forms.data.user_access_right.object[child.accessRightType+child.id];
                                        if(childrenRights && childrenRights.roles.length > 0) {
                                            accessRight.roles.push(readRole);
                                            break;
                                        }
                                    }
                                }
                                if(accessRight.roles.length > 0) {
                                    $scope.parentsViewInheritedRights[object.accessRightType+object.id] = accessRight;
                                }

                                // If we have any right (inherited or not) on the object, we can also VIEW
                                var baseRoles = $scope.forms.inheritedData.user_access_right.object.hasOwnProperty(object.accessRightType+object.id)?
                                        $scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id].roles:
                                        []
                                    ;
                                baseRoles = baseRoles.concat($scope.forms.data.user_access_right.object.hasOwnProperty(object.accessRightType+object.id)?
                                    $scope.forms.data.user_access_right.object[object.accessRightType+object.id].roles:
                                    []);
                                var canAnything = baseRoles.length > 0 || accessRight.roles.length > 0;
                                if(canAnything) {
                                    var canView = false;
                                    var allRoles = baseRoles.concat(accessRight.roles);
                                    for(var k=0;k<0;k++) {
                                        var role2 = allRoles[k];
                                        if(role2.role.indexOf('READ')>-1 || role2.role.indexOf('VIEW')>-1) {
                                            canView = true;
                                            break;
                                        }
                                    }
                                    if(!canView) {
                                        if(!$scope.parentsViewInheritedRights.hasOwnProperty(object.accessRightType+object.id)) {
                                            $scope.parentsViewInheritedRights[object.accessRightType+object.id] = new SecurityAccessRightObject({
                                                accessRightType: object.accessRightType,
                                                objectId: object.id,
                                                roles: []
                                            });
                                        }
                                        $scope.parentsViewInheritedRights[object.accessRightType+object.id].roles.push(readRole);
                                    }
                                }
                            };
                        })(object)));
                    }
                    return $q.all(promises);
                };
            })(flattenObjectsReverse));
        });
    };

    // This updates inherited roles for object from class (you can READ every object of class Page if you can READ class Page)
    function updateInheritedDataRecursive(objects, className, previousAccessRightType) {
        var promises = [];
        for(var i in objects) {
            var object = objects[i];
            // We only load objects of the same type: we know how to compute complete inherited roles for every object
            if(previousAccessRightType && previousAccessRightType != object.accessRightType) {
                continue;
            }

            promises.push(SecurityClassesParent.getParent(className).then((function(object){
                return function(className){
                    var roles = $scope.forms.inheritedData.user_access_right.object.hasOwnProperty(object.accessRightType+object.id)?$scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id].roles:[];

                    // Role can come from already inherited data (from group for example)
                    if($scope.forms.inheritedData.user_access_right.classes.hasOwnProperty(className)) {
                        for(var l in $scope.forms.inheritedData.user_access_right.classes[className].roles) {
                            roles.push(angular.copy($scope.forms.inheritedData.user_access_right.classes[className].roles[l]));
                        }
                    }
                    // Or from current form data (if this user only can READ Page)
                    if($scope.forms.data.user_access_right.classes.hasOwnProperty(className)) {
                        for(var k in $scope.forms.data.user_access_right.classes[className].roles) {
                            roles.push(angular.copy($scope.forms.data.user_access_right.classes[className].roles[k]));
                        }
                    }
                    $scope.forms.inheritedData.user_access_right.object[object.accessRightType+object.id] = {
                        objectId: object.id,
                        type: object.accessRightType,
                        roles: roles
                    };
                    if(object.hasOwnProperty('childrenSecurityContextObject')) {
                        promises.push(updateInheritedDataRecursive(object.childrenSecurityContextObject, className, object.accessRightType));
                    }
                };
            })(object)));
        }

        return $q.all(promises);
    }

    function flattenChildren(obj) {
        var result = [];

        if(obj.hasOwnProperty('childrenSecurityContextObject')) {
            for(var i in obj.childrenSecurityContextObject) {
                var child = obj.childrenSecurityContextObject[i];
                result.push(child);

                var childrenOfChild = flattenChildren(child);
                for(var j in childrenOfChild) {
                    result.push(childrenOfChild[j]);
                }
            }
        }

        return result;
    }

    // Returns the parent objects of an object
    function getObjectParents(object, className) {
        var deferred = $q.defer();
        SecurityClassesHierarchy.getClassParents(className).then(function(parentClasses){
            var potentialParents = [];
            var promises = [];
            for(var i in parentClasses) {
                var parentClass = parentClasses[i];
                if($scope.objectsOfClass.hasOwnProperty(parentClass)) {
                    for(var j in $scope.objectsOfClass[parentClass]) {
                        potentialParents.push($scope.objectsOfClass[parentClass][j]);
                        var child = flattenChildren($scope.objectsOfClass[parentClass][j]);
                        for(var k in child) {
                            potentialParents.push(child[k]);
                        }
                    }
                }
                else {
                    promises.push($scope.loadObjectOfClass(parentClass).then(function(){
                        for(var j in $scope.objectsOfClass[parentClass]) {
                            potentialParents.push($scope.objectsOfClass[parentClass][j]);
                            var child = flattenChildren($scope.objectsOfClass[parentClass][j]);
                            for(var k in child) {
                                potentialParents.push(child[k]);
                            }
                        }
                    }));
                }
            }

            // Once we have all parent classes, we can lookup for parents
            $q.all(promises).then(function(){
                var parents = [object];

                var added = 0;
                do {
                    added = 0;
                    for(var k in potentialParents) {
                        var potentialParent = potentialParents[k];
                        for(var l in parents) {
                            var parent = parents[l];
                            if(potentialParent.hasOwnProperty('childrenSecurityContextObject')) {
                                for(var m in potentialParent.childrenSecurityContextObject) {
                                    var children = potentialParent.childrenSecurityContextObject[m];
                                    // This checks if the base object is contained inside the potential parent's children
                                    if(parents.indexOf(potentialParent) == -1 && children.accessRightType == parent.accessRightType && children.id == parent.id) {
                                        added++;
                                        parents.push(potentialParent);
                                    }
                                }
                            }
                        }
                    }
                }
                while(added > 0);

                deferred.resolve(parents);
            });
        });

        return deferred.promise;
    }

    $scope.saveUserAccessRights = function(originalAccessRights) {

        //defined structure as used for Api Form
        var user_access_right = {
            accessRights:[]
        };
        //formatting data according to api form put user_access_rights from factory
        user_access_right.accessRights = SecurityAccessRightFactory.toRawData(originalAccessRights);

        var put_user_access_right = {};
        put_user_access_right['user_access_right'] = user_access_right;

        return SecurityAccessRightFactory.putUserAccessRights($stateParams.id, put_user_access_right).then(function() {
            // remove dirty state on form
            if (undefined != $scope.forms.params.user_access_right.formController) {
                $scope.forms.params.user_access_right.formController.$setPristine();
            }
        }, function(response) {
            $log.error('Error while updating user accessrights', response);
            NotificationService.addError(Translator.trans('notification.error.accessrights.update'), response);

            // forward rejection
            return $q.reject(response);
        });
    };

    // Loads objects of a certain type
    $scope.loadObjectOfClass = function(className) {
        if($scope.objectsOfClass.hasOwnProperty(className)) {
            return;
        }
        $scope.objectsOfClass[className] = [];

        $scope.mainContentLoading();
        return SecurityAccessRightFactory.getObjectOfClass(className).then(function(response){
            $scope.objectsOfClass[className] = response.data.objects;
            loadObjects($scope.objectsOfClass[className]);
            $scope.mainContentLoaded();
            updateInheritedData();
        });
    };

    $scope.loadObjectOfChildrenClass = function(className) {
        SecurityClassesHierarchy.getClassChildren(className).then(function(classes) {
            for(var i in classes) {
                var subclass = classes[i];
                $scope.loadObjectOfClass(subclass);
            }
        });
    };

    var loadObjects = function(listObjects){

        for(var i = 0; i < listObjects.length; i++){
            if(undefined == $scope.forms.data.user_access_right.object[listObjects[i].accessRightType+listObjects[i].id]) {
                $scope.forms.data.user_access_right.object[listObjects[i].accessRightType+listObjects[i].id] = new SecurityAccessRightObject({
                    accessRightType: listObjects[i].accessRightType,
                    objectId: listObjects[i].id,
                    roles: listObjects[i].roles
                });
            }
            if(listObjects[i].childrenSecurityContextObject.length != 0){
                loadObjects(listObjects[i].childrenSecurityContextObject);
            }
        }
    };

    $scope.forms.params.user_access_right = {
        submitActive: true,
        submitLabel: Translator.trans('update'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.submit();
        },
        cancelAction: function() {
            $state.go('backoffice.security.user_list');
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('access.rights.have.not.been.saved.are.you.sure.you.want.to.continue')
    };

    $scope.submit = function(){
        $scope.forms.params.user_access_right.submitActive = false;

        if ($scope.user.isSuperAdmin) {
            return $scope.saveUser($scope.forms.data.user).then(function() {
                // remove dirty state on form
                if (undefined != $scope.forms.params.user_access_right.formController) {
                    $scope.forms.params.user_access_right.formController.$setPristine();
                }
                $state.go('backoffice.security.user_list');
                NotificationService.addSuccess(Translator.trans('notification.success.user.update'));
            });
        }
        else {
            return $q.all([$scope.saveUser($scope.forms.data.user), $scope.saveUserAccessRights($scope.forms.data.user_access_right)]).then(function() {
                $state.go('backoffice.security.user_list');
                NotificationService.addSuccess(Translator.trans('notification.success.user.update'));
            });
        }
    };

    $scope.resendEmail = function(){
        SecurityUserFactory.resendValidationEmail($stateParams.id).then(function(){
            NotificationService.addSuccess(Translator.trans('notification.success.user.validationemail'));
        });
    };
}]);
