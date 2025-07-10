/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:08:41
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsFileDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsFileFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache', 'baseStateName',
function($log, $scope, $rootScope, FormsBag, CmsFileFactory, $state, $stateParams, NotificationService, $timeout, $templateCache, baseStateName) {
    $log = $log.getInstance('CmsFileDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.formLocale = $rootScope.locale;
    $scope.showFormLocaleSelector = true;

    $scope.forms = new FormsBag();

    $scope.fileEditIsGranted = null;

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;

    $scope.openBreadcrumbElement = function(breadcrumbElement) {
        // if it is not a file, then it is a file type
        if (!breadcrumbElement.id) {
            $scope.openFileList(breadcrumbElement.name);
        }
        else {
            $scope.openFile(breadcrumbElement.id);
        }
    };

    $scope.stateGoBack = function(id) {
        $state.go('backoffice.cms.file_list', {cmsFileType: $stateParams.cmsFileType});
    };

    $scope.cmsFileTabs = {
        'main': {
            label: Translator.transChoice('cms.file', 1),
            icon: 'glyphicon-file',
            stateName: baseStateName,
            stateParams: {
                file_id: $stateParams.file_id,
                cmsFileType: $stateParams.cmsFileType
            }
        }
    };

    CmsFileFactory.getFile($stateParams.file_id, 'all').then(function(response) {
        var file = response.data.cmsFile;

        $scope.formFileTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_file_form',{ type: file.cmsFileType });
        $templateCache.remove($scope.formFileTemplateUrl);

        $scope.breadcrumb.elements = [
            {
                name: file.cmsFileType
            },
            file
        ];

        $scope.file = file;
        $scope.fileName = file.getName($scope.locale);
        $scope.fileEditIsGranted = response.data.cmsFileEditIsGranted;

        if (file.supportsComments) {
            $scope.cmsFileTabs.comments = {
                label: Translator.trans('comments'),
                icon: 'glyphicon-comment',
                stateName: baseStateName + '.comment_list',
                stateParams: {
                    file_id: $stateParams.file_id,
                    cmsFileType: $stateParams.cmsFileType
                }
            };
        }

        if (file.supportsProductItems) {
            $scope.cmsFileTabs.productItems = {
                label: Translator.trans('product.items'),
                icon: 'glyphicon-pro glyphicon-pro-package',
                stateName: baseStateName + '.product_item_list',
                stateParams: {
                    file_id: $stateParams.file_id,
                    cmsFileType: $stateParams.cmsFileType
                }
            };
        }

        $scope.forms.data.cms_file = file.toFormData();
        $scope.forms.infos.cms_file = file.buildFormInfos();

        var mediacenterWidgetConfig = {
            //action that will be triggered on widget button click
            onShow: function() {
                //activate mediacenter widget when shown for the first time
                if (!$state.includes(baseStateName + '.mediacenter')) $state.go(baseStateName + '.mediacenter', $stateParams);
            },
            containerId: 'azimutMediacenterWidget',
            //callback function name for widget
            callbackName: 'azimutMediacenterChooseMediaDeclinations',
            params: {
                statePrefix: baseStateName
            },
            buttonLabel: Translator.trans('browse')
        };

        $scope.forms.widgets.cms_file_mainAttachment_mediaDeclination = mediacenterWidgetConfig;
        $scope.forms.widgets.cms_file_complementaryAttachment1_mediaDeclination = mediacenterWidgetConfig;
        $scope.forms.widgets.cms_file_complementaryAttachment2_mediaDeclination = mediacenterWidgetConfig;
        $scope.forms.widgets.cms_file_complementaryAttachment3_mediaDeclination = mediacenterWidgetConfig;
        $scope.forms.widgets.cms_file_complementaryAttachment4_mediaDeclination = mediacenterWidgetConfig;

        // grouped callback for all mediaDeclination widgets
        // (instead of severals $scope.forms.widgets.cms_file_secondaryAttachments_0_mediaDeclination, ...)
        $scope.forms.widgets.cms_file_secondaryAttachments = {
            compound: {
                mediaDeclination: mediacenterWidgetConfig
            }
        };


        var cmsFileTextWidgetConfig = {
            //action that will be triggered on widget button click
            onShow: function() {
                //activate mediacenter widget when shown for the first time
                if (!$state.includes(baseStateName + '.mediacenter')) $state.go(baseStateName + '.mediacenter', $stateParams);
            },
            containerId: 'azimutMediacenterWidget',
            //callback function name for widget
            callbackName: 'azimutMediacenterChooseMediaDeclinations',
            params: {
                statePrefix: baseStateName
            }
        };

        // apply widget config to all tiny mce enabled fields
        $scope.forms.widgets.default_cms_file_az_tinymce = cmsFileTextWidgetConfig;

        /*
        // Example of field specific widget params, will overwride default one
        $scope.forms.widgets['cms_file_cmsFileType_text'] = cmsFileTextWidgetConfig;
        for (var i = $rootScope.locales.length - 1; i >= 0; i--) {
            $scope.forms.widgets['cms_file_cmsFileType_text_'+$rootScope.locales[i]] = cmsFileTextWidgetConfig;
        };
        */

        $scope.forms.params.cms_file = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.saveFile($scope.forms.data.cms_file);
            },
            cancelAction: function() {
                $scope.stateGoBack(file.id);
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('cms.file.has.not.been.saved.are.you.sure.you.want.to.continue'),
            confirmDirtyDataStateChangeIgnoreSubstatesOf: baseStateName
        };

        CmsFileFactory.getFilePublications(file.id).then(function (response) {
            $scope.filePublications = response.data.publications;
        });

        $scope.mainContentLoaded();
    }, function(response) {
        if (403 == response.data.error.code) {
            NotificationService.addCriticalError(Translator.trans('notification.error.cms_file.%id%.not.allowed', { 'id' : $stateParams.file_id }));
        }
        else {
            NotificationService.addCriticalError(Translator.trans('notification.error.cms_file.%id%.get', { 'id' : $stateParams.file_id }));
        }

        $scope.$parent.showContentView = false;
        return;
    });

    $scope.showFilePublications = false;
    $scope.toggleFilePublications = function() {
        $scope.showFilePublications = !$scope.showFilePublications;
    }


    $scope.saveFile = function(file) {
        return CmsFileFactory.updateFile(file).then(function(response) {
            $log.info('File has been updated', response);

            // remove dirty state on form
            if (undefined != $scope.forms.params.cms_file.formController) {
                $scope.forms.params.cms_file.formController.$setPristine();
            }

            $scope.stateGoBack(file.id);
            NotificationService.addSuccess(Translator.trans('notification.success.file.update'));

            // clear form error messages
            delete $scope.forms.errors.cms_file;
        }, function(response) {
            $log.error('Update file failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.file.update'), response);

            // display form error messages
            if (undefined != response.data.errors) {
                $scope.forms.errors.cms_file = response.data.errors;
            }
        });
    };

    $scope.openTab = function(tabName) {
        $state.go($scope.cmsFileTabs[tabName].stateName, $scope.cmsFileTabs[tabName].stateParams);
    }

    $scope.activeCmsFileTab = 'main';
    $scope.$on('$stateChangeSuccess', function(event, toState) {
        if (0 === toState.name.indexOf(baseStateName + '.comment_')) {
            $scope.activeCmsFileTab = 'comments';
        }
        if (0 === toState.name.indexOf(baseStateName + '.product_item_')) {
            $scope.activeCmsFileTab = 'productItems';
        }
        else if ($state.is(baseStateName)) {
            $scope.activeCmsFileTab = 'main';
        }
    });
}]);
