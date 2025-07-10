/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:07:31
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsNewFileController', [
'$log','$scope', '$rootScope','FormsBag', 'CmsFileFactory', '$state', '$stateParams','NotificationService', '$timeout', '$templateCache',
function($log, $scope, $rootScope, FormsBag, CmsFileFactory, $state, $stateParams, NotificationService, $timeout, $templateCache) {
    $log = $log.getInstance('CmsNewFileController');

    $scope.$parent.showContentView = true;

    $scope.formFileTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_file_form',{ type: $stateParams.cmsFileType });
    $templateCache.remove($scope.formFileTemplateUrl);

    $scope.formLocale = $rootScope.locale;
    $scope.showFormLocaleSelector = true;

    $scope.forms = new FormsBag();

    $scope.cmsFileType = $stateParams.cmsFileType;

    //TODO : check supported types

    $scope.forms.data.cms_file = {
        type: $stateParams.cmsFileType,
        autoMetas: true,
        cmsFileType : {}
    };

    var isMediacenterWidgetLoaded = false;

    var baseStateName = $state.current.name;

    var mediacenterWidgetConfig = {

        //action that will be triggered on widget button click
        onShow: function() {
            //activate mediacenter widget when shown for the first time
            if(!isMediacenterWidgetLoaded) $state.go($state.current.name+'.mediacenter', $stateParams);
            isMediacenterWidgetLoaded = true;
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

    $scope.forms.widgets.cms_file_secondaryAttachments = {
        compound: {
            mediaDeclination: mediacenterWidgetConfig
        }
    };

    $scope.forms.widgets.cms_file_complementaryAttachment1_mediaDeclination = mediacenterWidgetConfig;
    $scope.forms.widgets.cms_file_complementaryAttachment2_mediaDeclination = mediacenterWidgetConfig;
    $scope.forms.widgets.cms_file_complementaryAttachment3_mediaDeclination = mediacenterWidgetConfig;
    $scope.forms.widgets.cms_file_complementaryAttachment4_mediaDeclination = mediacenterWidgetConfig;

    var cmsFileTextWidgetConfig = {
        //action that will be triggered on widget button click
        onShow: function() {
            //activate mediacenter widget when shown for the first time
            if(!isMediacenterWidgetLoaded) $state.go($state.current.name+'.mediacenter', $stateParams);
            isMediacenterWidgetLoaded = true;
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

    $scope.stateGoBack = function(file) {
        $state.go('backoffice.cms.file_list', {cmsFileType: $stateParams.cmsFileType});
    };

    $scope.forms.params.cms_file = {
        submitActive: true,
        submitLabel: Translator.trans('create'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addFile($scope.forms.data.cms_file);
        },
        cancelAction: function() {
            $scope.stateGoBack();
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('cms.file.has.not.been.saved.are.you.sure.you.want.to.continue'),
        confirmDirtyDataStateChangeIgnoreSubstatesOf: baseStateName
    };

    $scope.addFile = function(file) {
        return CmsFileFactory.createFile(file).then(function (response) {
            $log.info("File has been created", file);

            // remove dirty state on form
            if (undefined != $scope.forms.params.cms_file.formController) {
                $scope.forms.params.cms_file.formController.$setPristine();
            }

            $scope.stateGoBack(response.data.cmsFile);
            NotificationService.addSuccess(Translator.trans('notification.success.file.create'));

            // clear form error messages
            delete $scope.forms.errors.cms_file;

        }, function(response) {

            $log.error('Unable to create file: ' + response);
            NotificationService.addError(Translator.trans('notification.error.file.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.cms_file = response.data.errors;
            }

        });
    };
}]);
