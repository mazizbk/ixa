/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 12:22:49
 */

'use strict';

angular.module('azimutFrontofficeSecurity.controller')

.controller('FrontofficeSecurityNewUserController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService','FormsBag', 'FrontofficeSecurityUserFactory', '$timeout', '$templateCache',
function($log, $scope, $state, $stateParams, NotificationService, FormsBag, FrontofficeSecurityUserFactory, $timeout, $templateCache) {
    $log = $log.getInstance('FrontofficeSecurityNewUserController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.forms.data.frontoffice_user = {};

    $scope.addUser = function(userData) {
        return FrontofficeSecurityUserFactory.createUser(userData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.frontoffice_user.formController) {
                $scope.forms.params.frontoffice_user.formController.$setPristine();
            }

            $state.go('backoffice.frontofficesecurity.user_list');

            NotificationService.addSuccess(Translator.trans('notification.success.user.create'));

            // clear form error messages
            delete $scope.forms.errors.frontoffice_user;
        }, function(response) {
            $log.error('Create frontoffice user failed', response);
            NotificationService.addError(Translator.trans('notification.error.user.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.frontoffice_user = response.data.errors;
            }
        });
    }

    $scope.formUserTemplateUrl = Routing.generate('azimut_frontofficesecurity_backoffice_jsview_user_form');

    $scope.forms.params = {
        frontoffice_user: {
            submitActive: true,
            submitLabel: Translator.trans('create.frontoffice.user'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.addUser($scope.forms.data.frontoffice_user);
            },
            cancelAction: function() {
                $state.go('backoffice.frontofficesecurity.user_list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('user.has.not.been.saved.are.you.sure.you.want.to.continue')
        }
    }
}]);
