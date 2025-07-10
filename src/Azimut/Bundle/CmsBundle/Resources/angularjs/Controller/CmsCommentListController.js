/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 10:42:56
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsCommentListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'CmsCommentFactory', 'baseStateName',
function($log, $scope, $state, $stateParams, NotificationService, CmsCommentFactory, baseStateName) {
    $log = $log.getInstance('CmsCommentListController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.baseStateName = baseStateName;

    CmsCommentFactory.getComments($stateParams.file_id).then(function(response) {
        $scope.comments = response.data.comments;
        $scope.mainContentLoaded();
    });

    $scope.openComment = function(comment) {
        $state.go($scope.baseStateName + '.comment_detail', { comment_id: comment.id });
    };

    $scope.openNewComment = function() {
        $state.go($scope.baseStateName + '.comment_new');
    };

    $scope.validateComment = function(comment) {
        CmsCommentFactory.validateComment(comment).then(function (response) {
            $log.info('Comment has been validated', response);
            NotificationService.addSuccess(Translator.trans('notification.success.comment.validate'));
            angular.merge(comment, response.data.comment);
        }, function(response) {
            $log.error('Error while validating comment', response);
            NotificationService.addError(Translator.trans('notification.error.comment.validate'), response);
        });
    };

    $scope.unvalidateComment = function(comment) {
        CmsCommentFactory.unvalidateComment(comment).then(function (response) {
            $log.info('Comment has been unvalidated', response);
            NotificationService.addSuccess(Translator.trans('notification.success.comment.unvalidate'));
            angular.merge(comment, response.data.comment);
        }, function(response) {
            $log.error('Error while unvalidating comment', response);
            NotificationService.addError(Translator.trans('notification.error.comment.unvalidate'), response);
        });
    };

    $scope.deleteComment = function(comment) {
        CmsCommentFactory.deleteComment(comment).then(function (response) {
            $log.info('Comment has been deleted', response);
            $scope.comments.splice($scope.comments.indexOf(comment), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.comment.delete'));
        }, function(response) {
            $log.error('Error while deleting comment', response);
            NotificationService.addError(Translator.trans('notification.error.comment.delete'), response);
        });
    };

    $scope.visibleFilter = true;
}]);
