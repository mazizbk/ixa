/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 16:23:29
 */

'use strict';

angular.module('azimutModeration.controller')

.controller('CmsFileBufferDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsFileBufferFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache',
function($log, $scope, $rootScope, FormsBag, CmsFileBufferFactory, $state, $stateParams, NotificationService, $timeout, $templateCache) {
    $log = $log.getInstance('CmsFileBufferDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.forms = new FormsBag();

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;

    var baseStateName = $state.current.name;

    $scope.imgSrc = null;

    $scope.openBreadcrumbElement = function(breadcrumbElement) {
        // if it is not a file, then it is a file type
        if(!breadcrumbElement.id) {
            $state.go('backoffice.moderation.cms_file_buffer_list');
        }
        else {
            $scope.openFile(breadcrumbElement.id);
        }
    };

    $scope.stateGoBack = function(id) {
        $state.go('backoffice.moderation.cms_file_buffer_list', {cmsFileBufferType: $stateParams.cmsFileBufferType});
    };

    CmsFileBufferFactory.getFile($stateParams.id).then(function(response) {
        var file = response.data.cmsFileBuffer;
        var cmsFileBufferFileMimeType = response.data.cmsFileBufferFileMimeType;

        $scope.formFileTemplateUrl = Routing.generate('azimut_moderation_backoffice_jsview_cms_file_buffer_form',{ type: file.cmsFileBufferType });
        $templateCache.remove($scope.formFileTemplateUrl);

        $scope.breadcrumb.elements = [
            {
                name: file.cmsFileBufferType
            },
            file
        ];

        $scope.file = file;

        //HACK : 'quick' solution, move all file specific fields to a fileType subobject
        var fileFormData = {
            id: file.id,
            locale: file.locale,
            type: file.cmsFileBufferType,
            cmsFileBufferType: angular.copy(file),
        };

        delete fileFormData.cmsFileBufferType.id;
        delete fileFormData.cmsFileBufferType.locale;
        delete fileFormData.cmsFileBufferType.cmsFileBufferType;
        delete fileFormData.cmsFileBufferType.domainName;
        delete fileFormData.cmsFileBufferType.name;
        delete fileFormData.cmsFileBufferType.filePath;
        delete fileFormData.cmsFileBufferType.isArchived;
        delete fileFormData.cmsFileBufferType.targetPagePath;
        delete fileFormData.cmsFileBufferType.targetZoneId;
        delete fileFormData.cmsFileBufferType.targetZoneName;
        delete fileFormData.cmsFileBufferType.userIp;
        delete fileFormData.cmsFileBufferType.userEmail;
        delete fileFormData.cmsFileBufferType.userLocale;
        //END HACK

        $scope.forms.data.cms_file_buffer = fileFormData;

        $scope.forms.params.cms_file_buffer = {
            submitActive: true,
            submitLabel: Translator.trans('validate'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                $scope.convertFile($scope.forms.data.cms_file_buffer);
            },
            cancelAction: function() {
                $scope.stateGoBack();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('cms.file.buffer.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();

        if (undefined != file.filePath && ('image/jpeg' == cmsFileBufferFileMimeType || 'image/png' == cmsFileBufferFileMimeType || 'image/gif' == cmsFileBufferFileMimeType)) {
            $scope.imgSrc = Routing.generate('azimut_moderation_file_proxy_thumb',{ filepath: file.filePath, size: 'l' });
        }

    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.cms_file_buffer.%id%.get', { 'id' : $stateParams.id }));

        $scope.$parent.showContentView = false;
        return;
    });

 $scope.convertFile = function(fileData) {
        CmsFileBufferFactory.convertFile(fileData).then(function (response) {
            $log.info('File has been validated', response);
            // remove dirty state on form
            if (undefined != $scope.forms.params.cms_file_buffer.formController) {
                $scope.forms.params.cms_file_buffer.formController.$setPristine();
            }
            NotificationService.addSuccess(Translator.trans('notification.success.file.validate'));
            $scope.stateGoBack();
        }, function(response) {
            $log.error('Error while validating file', response);
            NotificationService.addError(Translator.trans('notification.error.file.validate'), response);
        });
    };
}]);
