/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 10:53:36
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsCommentDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsCommentFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache', 'baseStateName',
function($log, $scope, $rootScope, FormsBag, CmsCommentFactory, $state, $stateParams, NotificationService, $timeout, $templateCache, baseStateName) {
    $log = $log.getInstance('CmsCommentDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.forms = new FormsBag();

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;
    $scope.baseStateName = baseStateName;

    $scope.stateGoBack = function() {
        $state.go($scope.baseStateName + '.comment_list');
    };

    CmsCommentFactory.getComment($stateParams.comment_id, $stateParams.file_id).then(function(response) {
        var comment = response.data.comment;

        $scope.formCommentTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_comment_form', { action: 'update' });
        // $templateCache.remove($scope.formCommentTemplateUrl);

        $scope.comment = comment;
        $scope.forms.data.comment = angular.copy(comment);

        $scope.forms.params.comment = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return CmsCommentFactory.updateComment($scope.forms.data.comment).then(function(response) {
                    $log.info('Comment has been updated', response);

                    // remove dirty state on form
                    if (undefined != $scope.forms.params.comment.formController) {
                        $scope.forms.params.comment.formController.$setPristine();
                    }
                    $scope.stateGoBack();
                    NotificationService.addSuccess(Translator.trans('notification.success.comment.update'));
                }, function(response) {
                    NotificationService.addError(Translator.trans('notification.error.comment.update'), response);

                    // display form error messages
                    if(undefined != response.data.errors) {
                        $scope.forms.errors.comment = response.data.errors;
                    }
                });
            },
            cancelAction: function() {
                $scope.stateGoBack();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('comment.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.comment.%id%.get', { 'id' : $stateParams.comment_id }));

        $scope.$parent.showContentView = false;
        return;
    });
}]);
