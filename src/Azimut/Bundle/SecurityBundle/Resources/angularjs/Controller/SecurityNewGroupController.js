/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-13 12:04:51
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityNewGroupController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService','FormsBag', 'SecurityGroupFactory',
function($log, $scope, $state, $stateParams, NotificationService, FormsBag, SecurityGroupFactory) {
    $log = $log.getInstance('SecurityNewGroupController');

    $scope.formGroupTemplateUrl = Routing.generate('azimut_security_backoffice_jsview_group_form');

    $scope.forms = new FormsBag();

    $scope.forms.data.group = {};

    $scope.forms.params.group = {
        submitActive: true,
        submitLabel: Translator.trans('create'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addGroup($scope.forms.data.group);
        },
        cancelAction: function() {
            $state.go('backoffice.security.group_list');
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('group.has.not.been.saved.are.you.sure.you.want.to.continue')
    };

    $scope.addGroup = function(group) {
        return SecurityGroupFactory.createGroup(group).then(function (response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.group.formController) {
                $scope.forms.params.group.formController.$setPristine();
            }

            $state.go('backoffice.security.group_list');
            NotificationService.addSuccess(Translator.trans('notification.success.group.create'));

            // clear form error messages
            delete $scope.forms.errors.group;
        }, function(response) {
            $log.error('Unable to create group: ', response);
            NotificationService.addError(Translator.trans('notification.error.group.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.group = response.data.errors;
            }
        });
    }
}]);
