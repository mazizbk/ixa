/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:33:43
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeSiteDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'FrontofficeSiteFactory', '$state', '$stateParams', 'NotificationService', '$timeout', 'azConfirmModal', '$window',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteFactory, $state, $stateParams, NotificationService, $timeout, azConfirmModal, $window) {
    $log = $log.getInstance('FrontofficeSiteDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    var site = FrontofficeSiteFactory.findSite($stateParams.id);

    if(!site) {
        NotificationService.addCriticalError(Translator.trans('notification.error.site.with.id.%id%.get', { 'id' : $stateParams.id }));
        $scope.$parent.showContentView = false;
        return;
    }

    $scope.site = site;

    $scope.formSiteTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_site_form');

    $scope.sitePanelParams = {
        collapsed: true
    };

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    //Fetch the complete version of the site, with all fields
    FrontofficeSiteFactory.getSite(site.id, 'all').then(function(response) {
        var site = angular.copy(response.data.site);

        // we don't use the real Site object because we need raw data to be binded into the form
        $scope.forms.data.site = site.toFormData();

        var currentSiteLayoutId = site.layout.id;

        var submitFunction = function() {
            return $scope.saveSite($scope.forms.data.site);
        };

        $scope.forms.params.site = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                // ask confirmation if layout changed
                if(currentSiteLayoutId != $scope.forms.data.site.layout) {
                    azConfirmModal(Translator.trans('site.layout.confirm.change')).result.then(submitFunction);
                }
                else {
                    return submitFunction();
                }
            },
            cancelAction: function() {
                $scope.forms.data.site = site.toFormData();
                $scope.forms.params.site.formController.$setPristine();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('site.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();
    });

    $scope.breadcrumb = {
        elements: [site]
    };

    $scope.saveSite = function(siteData) {
        return FrontofficeSiteFactory.updateSite(siteData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.site.formController) {
                $scope.forms.params.site.formController.$setPristine();
            }

            // as we don't leave the form page but just hide the panel, we need to update the form data
            var site = response.data.site;
            var formController = $scope.forms.params.site.formController;
            $scope.forms.data.site = site.toFormData();
            $scope.forms.params.site.formController = formController;


            NotificationService.addSuccess(Translator.trans('notification.success.site.update'));

            $scope.sitePanelParams.collapsed = true;

            // clear form error messages
            delete $scope.forms.errors.site;

        }, function(response) {
            $log.error('Update site failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.site.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.site = response.data.errors;
            }
        });
    };

    $scope.openSitePreview = function(site) {
        $window.open(site.uri + Routing.generate('azimut_frontoffice', {'path': ''}), 'azimut.pagepreview','menubar=no,status=no,scrollbars=yes');
    };
}]);
