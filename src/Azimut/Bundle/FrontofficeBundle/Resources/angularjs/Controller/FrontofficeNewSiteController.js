/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:35:43
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeNewSiteController', [
'$log', '$scope', '$rootScope','FormsBag', 'FrontofficeSiteFactory', '$state', '$stateParams', 'NotificationService', '$timeout',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteFactory, $state, $stateParams, NotificationService, $timeout) {
    $log = $log.getInstance('FrontofficeNewSiteController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.forms.data = {
        site: {
            commentsActive: false,
            commentModerationActive: true,
            commentRatingActive: false,
            scheme: 'http',
        }
    };

    $scope.formLocale = $rootScope.locale;

    $scope.addSite = function(siteData) {
        return FrontofficeSiteFactory.createSite(siteData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.site.formController) {
                $scope.forms.params.site.formController.$setPristine();
            }

            $state.go('backoffice.frontoffice.list');

            NotificationService.addSuccess(Translator.trans('notification.success.site.create'));

            // clear form error messages
            delete $scope.forms.errors.site;

        }, function(response) {

            $log.error('Create site failed', response);
            NotificationService.addError(Translator.trans('notification.error.site.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.site = response.data.errors;
            }

        });
    }

    $scope.formSiteTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_site_form');

    $scope.forms.params = {
        site: {
            submitActive: true,
            submitLabel: Translator.trans('create.site'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.addSite($scope.forms.data.site);
            },
            cancelAction: function() {
                $state.go('backoffice.frontoffice.list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('site.has.not.been.saved.are.you.sure.you.want.to.continue')
        }
    }
}]);
