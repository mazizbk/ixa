/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:07:08
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsFileListController', [
'$log', '$scope', '$rootScope', 'CmsFileFactory', '$state', '$stateParams', 'NotificationService', 'DataSortDefinitionBuilder',
function($log, $scope, $rootScope, CmsFileFactory, $state, $stateParams, NotificationService, DataSortDefinitionBuilder) {
    $log = $log.getInstance('CmsFileListController');

    $scope.$parent.showContentView = true;

    $scope.files = CmsFileFactory.files();

    $scope.type = $stateParams.cmsFileType;

    $scope.waitingCmsFilesBufferCount = CmsFileFactory.waitingCmsFilesBufferCount();

    $scope.filesSortDefinitionBuilder = new DataSortDefinitionBuilder('cms-cmsfiles', [
        {
            'label': Translator.trans('creation.date'),
            'property': 'id',
            'reverse': true,
            'default': true
        },
        {
            'label': Translator.trans('name'),
            'property': 'name'
        },
        {
            'label': Translator.trans('type'),
            'property': 'cmsFileType'
        },
        {
            'label': Translator.trans('publications'),
            'property': 'publicationsCount',
            'reverse': true
        }
    ]);

    $scope.openFile = function(file) {
        $state.go('backoffice.cms.file_detail', {file_id: file.id, cmsFileType: file.cmsFileType});
    };

    $scope.deleteFile = function(file) {
        CmsFileFactory.deleteFile(file).then(function (response) {
            $log.info('File has been deleted', response);
            NotificationService.addSuccess(Translator.trans('notification.success.file.delete'));
        }, function(response) {
            $log.error('Error while deleting file', response);
            NotificationService.addError(Translator.trans('notification.error.file.delete'), response);
        });
    };

    $scope.setListView = function() {
        $scope.setFilesTemplateView('table');
    };

    $scope.setSummaryView = function() {
        $scope.setFilesTemplateView('summary');
    };

    $scope.setFilesTemplateView = function(templateName) {
        if('summary' != templateName && 'table' != templateName) {
            $log.error('Unsupported cms files template view "'+templateName+'"');
            return;
        }

        $scope.cmsFilesTemplateView = templateName;

        localStorage.setItem('azimutCms-files-template-view', templateName);
    };

    var storedCmsFilesTemplateView = localStorage.getItem('azimutCms-files-template-view');
    if(storedCmsFilesTemplateView) {
        $log.log('Restoring cms files template view from local storage', storedCmsFilesTemplateView);
        $scope.setFilesTemplateView(storedCmsFilesTemplateView);
    }
    else {
        $scope.setFilesTemplateView('table');
    }

}]);
