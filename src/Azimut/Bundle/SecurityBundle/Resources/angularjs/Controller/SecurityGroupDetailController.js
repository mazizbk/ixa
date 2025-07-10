/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-13 12:03:37
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityGroupDetailController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService','FormsBag', 'SecurityGroupFactory', 'SecurityAccessRightFactory', 'SecurityAccessRightObject', 'SecurityAccessRightClass', '$q',
function($log, $scope, $state, $stateParams, NotificationService, FormsBag, SecurityGroupFactory, SecurityAccessRightFactory, SecurityAccessRightObject, SecurityAccessRightClass, $q) {
    $log = $log.getInstance('SecurityGroupDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.formGroupTemplateUrl = Routing.generate('azimut_security_backoffice_jsview_group_update_form');

    $scope.forms = new FormsBag();

    SecurityGroupFactory.getGroup($stateParams.id).then(function(response) {

        $scope.forms.data.group = $scope.group = response.group;

        $scope.forms.params.group = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.submit();
            },
            cancelAction: function() {
                $state.go('backoffice.security.group_list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('group.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    });

    $scope.saveGroup = function(group) {
        return SecurityGroupFactory.updateGroup(group).then(function(response) {
            $log.info('Group updated', response);

            // remove dirty state on form
            if (undefined != $scope.forms.params.group.formController) {
                $scope.forms.params.group.formController.$setPristine();
            }

            // clear form error messages
            delete $scope.forms.errors.group;
        }, function(response) {
            $log.error('Update group failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.group.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.group = response.data.errors;
            }

            // forward rejection
            return $q.reject(response);
        });
    };

    // Access right
    $scope.appRoles = SecurityAccessRightFactory.getAppRoles(); // all the application roles
    $scope.roles = SecurityAccessRightFactory.getSimpleRoles(); // all the simple roles as create, view, edit

    $scope.groupAccessRightAppRoles = []; //var for application roles
    $scope.groupAccessRightClass = [];
    $scope.groupAccessRightObjectRoles = [];
    $scope.classes = SecurityAccessRightFactory.getClasses();
    $scope.objectsOfClass = [];
    $scope.headersOfClass = [];

    //get group access rights and sort them per category
    SecurityAccessRightFactory.getGroupAccessRights($stateParams.id).then(function(response) {
        //match group access rights with possible access rights
        $scope.forms.data.group_access_right = response.data.accessRights;

        $scope.forms.data.group_access_right.apps = $scope.forms.data.group_access_right.apps.toFormData();

        // transform classes array index to namespace
        var indexedClasses = {};
        for (var i = $scope.forms.data.group_access_right.classes.length - 1; i >= 0; i--) {
            indexedClasses[$scope.forms.data.group_access_right.classes[i].class] = $scope.forms.data.group_access_right.classes[i].toFormData();
        }

        $scope.forms.data.group_access_right.classes = indexedClasses;

        for(var className in $scope.classes) {

            if(undefined == $scope.forms.data.group_access_right.classes[className]) $scope.forms.data.group_access_right.classes[className] = new SecurityAccessRightClass({class: className});
        }

        //creating an indexed array of objects
        var indexedObjects= {};
        for (var i = $scope.forms.data.group_access_right.object.length - 1; i >= 0; i--) {
            //indexed with [type + objId]
            indexedObjects[$scope.forms.data.group_access_right.object[i].type+$scope.forms.data.group_access_right.object[i].objectId] = $scope.forms.data.group_access_right.object[i].toFormData();
        }

        $scope.forms.data.group_access_right.object = indexedObjects;

        $scope.mainContentLoaded();
    });

    $scope.saveGroupAccessRight = function(originalAccessRights) {
        //defined structure as used for Api Form
        var group_access_right = {
            accessRights:[]
        };
        //formatting data according to api form put user_access_rights from factory
        group_access_right.accessRights = SecurityAccessRightFactory.toRawData(originalAccessRights);

        var put_group_access_right = {};
        put_group_access_right['group_access_right'] = group_access_right;
        //PUT request to accessright API
        return SecurityAccessRightFactory.putGroupAccessRights($scope.group.id, put_group_access_right).then(function() {
            // remove dirty state on form
            if (undefined != $scope.forms.params.group_access_right.formController) {
                $scope.forms.params.group_access_right.formController.$setPristine();
            }
        }, function(response) {
            $log.error('Error while updatind group accessrights', response);
            NotificationService.addError(Translator.trans('notification.error.accessrights.update'), response);

            // forward rejection
            return $q.reject(response);
        });
    };

    var loadObjects = function(listObjects){

        for(var i = 0; i < listObjects.length; i++){
            if(undefined == $scope.forms.data.group_access_right.object[listObjects[i].accessRightType+listObjects[i].id]) {
                $scope.forms.data.group_access_right.object[listObjects[i].accessRightType+listObjects[i].id] = new SecurityAccessRightObject({
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

    $scope.loadObjectOfClass = function(className) {
        $scope.objectsOfClass[className] = [];

        SecurityAccessRightFactory.getObjectOfClass(className).then(function(response){
            $scope.objectsOfClass[className]= response.data.objects;
            loadObjects($scope.objectsOfClass[className]);
        });
        $scope.mainContentLoaded();
    };

    $scope.forms.params.group_access_right = {
        submitActive: true,
        submitLabel: Translator.trans('update'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.submit();
        },
        cancelAction: function() {
            $state.go('backoffice.security.group_list');
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('access.rights.have.not.been.saved.are.you.sure.you.want.to.continue')
    };

    $scope.submit = function() {
        $scope.forms.params.group_access_right.submitActive = false;

        return $q.all([$scope.saveGroup($scope.forms.data.group), $scope.saveGroupAccessRight($scope.forms.data.group_access_right)]).then(function(){
            $state.go('backoffice.security.group_list');
            NotificationService.addSuccess(Translator.trans('notification.success.group.update'));
        });
    };
}]);
