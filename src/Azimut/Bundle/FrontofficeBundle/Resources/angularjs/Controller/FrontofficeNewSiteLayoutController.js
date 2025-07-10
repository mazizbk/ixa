/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-02 09:34:53
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeNewSiteLayoutController', [
'$log', '$scope', '$rootScope','FormsBag', 'FrontofficeSiteLayoutFactory', '$state', '$stateParams', 'NotificationService', '$timeout',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteLayoutFactory, $state, $stateParams, NotificationService, $timeout) {
    $log = $log.getInstance('FrontofficeNewSiteLayoutController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.forms.data = {
        site_layout: {
        }
    };

    $scope.formLocale = $rootScope.locale;

    $scope.addSiteLayout = function(siteLayoutData) {
        return FrontofficeSiteLayoutFactory.createSiteLayout(siteLayoutData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.site_layout.formController) {
                $scope.forms.params.site_layout.formController.$setPristine();
            }

            $state.go('backoffice.frontoffice.site_layouts_list');

            NotificationService.addSuccess(Translator.trans('notification.success.site_layout.create'));

            // clear form error messages
            delete $scope.forms.errors.site_layout;

        }, function(response) {
            $log.error('Create site layout failed', response);
            NotificationService.addError(Translator.trans('notification.error.site_layout.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.site_layout = response.data.errors;
            }
        });

    }

    $scope.formSiteLayoutTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_site_layout_form');

    $scope.forms.params = {
        site_layout: {
            submitActive: true,
            submitLabel: Translator.trans('create.site_layout'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.addSiteLayout($scope.forms.data.site_layout);
            },
            cancelAction: function() {
                $state.go('backoffice.frontoffice.site_layouts_list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('site_layout.has.not.been.saved.are.you.sure.you.want.to.continue')
        }
    }
}]);
