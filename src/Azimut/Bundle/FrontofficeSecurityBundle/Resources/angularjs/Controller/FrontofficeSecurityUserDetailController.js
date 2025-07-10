/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 12:22:49
 */

'use strict';

angular.module('azimutFrontofficeSecurity.controller')

.controller('FrontofficeSecurityUserDetailController', [
'$log', '$scope', '$state', '$stateParams', '$q', 'NotificationService','FormsBag', 'FrontofficeSecurityUserFactory', 'SecurityAccessRightFactory', 'SecurityAccessRightClass', 'SecurityAccessRightObject', 'SecurityClassesHierarchy', 'SecurityClassesParent', 'SecurityClassesSecurityType', '$templateCache',
function($log, $scope, $state, $stateParams, $q, NotificationService, FormsBag, FrontofficeSecurityUserFactory, SecurityAccessRightFactory, SecurityAccessRightClass, SecurityAccessRightObject, SecurityClassesHierarchy, SecurityClassesParent, SecurityClassesSecurityType, $templateCache) {
    $log = $log.getInstance('FrontofficeSecurityUserDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.forms = new FormsBag();

    $scope.userEditIsGranted = null;

    $scope.stateGoBack = function() {
        $state.go('backoffice.frontofficesecurity.user_list');
    };

    FrontofficeSecurityUserFactory.getUser($stateParams.id).then(function(response) {
        var user = response.data.user;

        $scope.formUserTemplateUrl = Routing.generate('azimut_frontofficesecurity_backoffice_jsview_user_update_form');
        $templateCache.remove($scope.formUserTemplateUrl);

        $scope.user = user;

        $scope.forms.data.frontoffice_user = user;

        $scope.forms.params.frontoffice_user = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.saveUser($scope.forms.data.frontoffice_user);
            },
            cancelAction: function() {
                $scope.stateGoBack();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('user.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.frontoffice.user.%id%.get', { 'id': $stateParams.id }));

        $scope.$parent.showContentView = false;
        return;
    });


    $scope.saveUser = function(user) {
        return FrontofficeSecurityUserFactory.updateUser(user).then(function(response) {
            $log.info('User has been updated', response);

            // remove dirty state on form
            if (undefined != $scope.forms.params.frontoffice_user.formController) {
                $scope.forms.params.frontoffice_user.formController.$setPristine();
            }

            $scope.stateGoBack();
            NotificationService.addSuccess(Translator.trans('notification.success.user.update'));

            // clear form error messages
            delete $scope.forms.errors.frontoffice_user;
        }, function(response) {
            $log.error('Update user failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.user.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.frontoffice_user = response.data.errors;
            }
        });
    };
}]);
