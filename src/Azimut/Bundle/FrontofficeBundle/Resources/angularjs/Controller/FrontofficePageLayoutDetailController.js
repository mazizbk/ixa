/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 10:39:47
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficePageLayoutDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'FrontofficePageLayoutFactory', '$state', '$stateParams', 'NotificationService', '$timeout', 'azConfirmModal', '$window',
function($log, $scope, $rootScope, FormsBag, FrontofficePageLayoutFactory, $state, $stateParams, NotificationService, $timeout, azConfirmModal, $window) {
    $log = $log.getInstance('FrontofficePageLayoutDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.formPageLayoutTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_page_layout_form');

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    // Fetch the complete version of the page, with all fields
    FrontofficePageLayoutFactory.getPageLayout($stateParams.id, 'all').then(function(pageLayout) {
        $scope.pageLayout = angular.copy(pageLayout);

        // Transform template options array to splitted fields
        var templateOptions = {};
        for (var templateOption in pageLayout.templateOptions) {
            templateOptions[templateOption] = {};
            templateOptions[templateOption].key = templateOption;
            templateOptions[templateOption].value = pageLayout.templateOptions[templateOption];
        }
        pageLayout.templateOptions = templateOptions;

        // we don't use the real PageLayout object because we need raw data to be binded into the form
        $scope.forms.data.page_layout = pageLayout;

        var submitFunction = function() {
            return $scope.savePageLayout($scope.forms.data.page_layout);
        };

        $scope.forms.params.page_layout = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return submitFunction();
            },
            cancelAction: function() {
                $scope.forms.data.page_layout = pageLayout;
                $scope.forms.params.page_layout.formController.$setPristine();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('page_layout.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.page_layout.with.id.%id%.get', { 'id' : $stateParams.id }));
        $scope.$parent.showContentView = false;
    });

    $scope.savePageLayout = function(pageLayoutData) {
        return FrontofficePageLayoutFactory.updatePageLayout(pageLayoutData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.page_layout.formController) {
                $scope.forms.params.page_layout.formController.$setPristine();
            }

            $state.go('backoffice.frontoffice.page_layouts_list');

            NotificationService.addSuccess(Translator.trans('notification.success.page_layout.update'));

            // clear form error messages
            delete $scope.forms.errors.page_layout;
        }, function(response) {
            $log.error('Update page layout failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.page_layout.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.page_layout = response.data.errors;
            }
        });
    };
}]);
