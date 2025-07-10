/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-19 16:14:05
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsNewCommentController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsCommentFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache', 'baseStateName',
function($log, $scope, $rootScope, FormsBag, CmsCommentFactory, $state, $stateParams, NotificationService, $timeout, $templateCache, baseStateName) {
    $log = $log.getInstance('CmsNewCommentController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;
    $scope.baseStateName = baseStateName;

    $scope.stateGoBack = function() {
        $state.go($scope.baseStateName + '.comment_list');
    };

    $scope.formCommentTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_comment_form', { action: 'create' });
    // $templateCache.remove($scope.formCommentTemplateUrl);

    $scope.forms.data.comment = {
        'cmsFile': $stateParams.file_id,
        'isVisible': true,
    };

    $scope.forms.params.comment = {
        submitActive: true,
        submitLabel: Translator.trans('create'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            CmsCommentFactory.createComment($scope.forms.data.comment).then(function(response) {
                $log.info('Comment has been created', response);

                // remove dirty state on form
                if (undefined != $scope.forms.params.comment.formController) {
                    $scope.forms.params.comment.formController.$setPristine();
                }
                $scope.stateGoBack();
                NotificationService.addSuccess(Translator.trans('notification.success.comment.create'));
            }, function(response) {
                NotificationService.addError(Translator.trans('notification.error.comment.create'), response);

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
}]);
