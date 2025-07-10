/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 12:13:43
 */

'use strict';

angular.module('azimutModeration.controller')

.controller('CmsFileBufferListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'CmsFileBufferFactory',
function($log, $scope, $state, $stateParams, NotificationService, CmsFileBufferFactory) {
    $log = $log.getInstance('CmsFileBufferListController');

    $scope.$parent.showContentView = true;
    $scope.targetZoneId = ('' == $stateParams.targetZoneId)? undefined : $stateParams.targetZoneId;
    $scope.cmsFileBufferType = ('' == $stateParams.cmsFileBufferType)? undefined : $stateParams.cmsFileBufferType;

    $scope.files = CmsFileBufferFactory.files();

    $scope.openFile = function(file) {
        $state.go('backoffice.moderation.cms_file_buffer_detail', {id: file.id, cmsFileBufferType: file.cmsFileBufferType});
    };

    $scope.openUser = function(userId) {
        $state.go('backoffice.frontofficesecurity.user_detail', {id: userId});
    };

    $scope.deleteFile = function(file) {
        CmsFileBufferFactory.deleteFile(file).then(function (response) {
            $log.info('file has been deleted', response);
            NotificationService.addSuccess(Translator.trans('notification.success.file.delete'));
        }, function(response) {
            $log.error('error while deleting file', response);
            NotificationService.addError(Translator.trans('notification.error.file.delete'), response);
        });
   };
}]);
