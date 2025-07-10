/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 15:19:36
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeSiteLayoutDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'FrontofficeSiteLayoutFactory', '$state', '$stateParams', 'NotificationService', '$timeout', 'azConfirmModal', '$window',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteLayoutFactory, $state, $stateParams, NotificationService, $timeout, azConfirmModal, $window) {
    $log = $log.getInstance('FrontofficeSiteLayoutDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    FrontofficeSiteLayoutFactory.getSiteLayout($stateParams.id).then(function(siteLayout) {
        $scope.siteLayout = siteLayout;
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.site_layout.with.id.%id%.get', { 'id' : $stateParams.id }));
        $scope.$parent.showContentView = false;
    });

    $scope.formSiteLayoutTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_site_layout_form');

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    // Fetch the complete version of the site, with all fields
    FrontofficeSiteLayoutFactory.getSiteLayout($stateParams.id, 'all').then(function(siteLayout) {
        $scope.siteLayout = angular.copy(siteLayout);

        // We don't use the real SiteLayout object because we need raw data to be binded into the form
        $scope.forms.data.site_layout = siteLayout;

        var submitFunction = function() {
            return $scope.saveSiteLayout($scope.forms.data.site_layout);
        };

        $scope.forms.params.site_layout = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return submitFunction();
            },
            cancelAction: function() {
                $scope.forms.data.site_layout = siteLayout;
                $scope.forms.params.site_layout.formController.$setPristine();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('site_layout.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    });

    $scope.saveSiteLayout = function(siteLayoutData) {
        return FrontofficeSiteLayoutFactory.updateSiteLayout(siteLayoutData).then(function(response) {
            // Remove dirty state on form
            if (undefined != $scope.forms.params.site_layout.formController) {
                $scope.forms.params.site_layout.formController.$setPristine();
            }

            $state.go('backoffice.frontoffice.site_layouts_list');

            NotificationService.addSuccess(Translator.trans('notification.success.site_layout.update'));

            // Clear form error messages
            delete $scope.forms.errors.site_layout;
        }, function(response) {
            $log.error('Update site layout failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.site_layout.update'), response);

            // Display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.site_layout = response.data.errors;
            }
        });
    };
}]);
