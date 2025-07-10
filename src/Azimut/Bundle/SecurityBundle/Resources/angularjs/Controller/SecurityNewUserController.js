/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-12 16:22:06
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityNewUserController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService','FormsBag', 'SecurityUserFactory', '$timeout', '$templateCache',
function($log, $scope, $state, $stateParams, NotificationService, FormsBag, SecurityUserFactory, $timeout, $templateCache) {
    $scope.formUserTemplateUrl = Routing.generate('azimut_security_backoffice_jsview_user_form');
    $templateCache.remove($scope.formUserTemplateUrl);

    $scope.forms = new FormsBag();

    $scope.forms.data.user = {};

    $scope.forms.params.user = {
        submitActive: true,
        submitLabel: Translator.trans('create'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addUser($scope.forms.data.user);
        },
        cancelAction: function() {
            $state.go('backoffice.security.user_list');
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('user.has.not.been.saved.are.you.sure.you.want.to.continue')
    };

    //form values contains affectables values for compounds checkboxes (groups here)
    $scope.forms.values.user = {
        groups: $scope.groups
    };

    $scope.addUser = function(user) {
        return SecurityUserFactory.createUser(user).then(function (response) {
            $log.info("User created", response);

            // remove dirty state on form
            if (undefined != $scope.forms.params.user.formController) {
                $scope.forms.params.user.formController.$setPristine();
            }

            var user = response.user;

            // we have to provide state params, even if empty, otherwise state transition will occurs twice, see UI-router issue #350 https://github.com/angular-ui/ui-router/issues/350
            $state.go('backoffice.security.user_detail',{id: user.id});
            NotificationService.addSuccess(Translator.trans('notification.success.user.create'));

            // clear form error messages
            delete $scope.forms.errors.user;

        }, function(response) {
            $log.error('Unable to create user: ' + response);
            NotificationService.addError(Translator.trans('notification.error.user.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.user = response.data.errors;
            }
        });
    };

    var _findTimeout, emailRegex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i;
    $scope.findUserByEmail = function() {
        $scope.findDirty = true;
        if(_findTimeout){
            $timeout.cancel(_findTimeout);
        }
        _findTimeout = $timeout(function(){
            _findTimeout = null;

            $scope.forms.data.user.firstName = null;
            $scope.forms.data.user.lastName = null;

            if($scope.forms.errors.hasOwnProperty('user') && $scope.forms.errors.user.hasOwnProperty('username')) {
                return;
            }

            var email = $scope.forms.data.user.username;
            if(!emailRegex.test(email)){
                return;
            }
            $scope.findLoading = true;
            SecurityUserFactory.findUserByEmailFromLogin(email).then(function(result){
                $scope.findDirty = false;
                if(angular.isObject(result.data)) {
                    var user = result.data;
                    $scope.forms.data.user.firstName = user.firstName;
                    $scope.forms.data.user.lastName = user.lastName;
                }
            }).finally(function(){
                $scope.findDirty = false;
                $scope.findLoading = false;
            });

        }, 500);
    };
}]);
