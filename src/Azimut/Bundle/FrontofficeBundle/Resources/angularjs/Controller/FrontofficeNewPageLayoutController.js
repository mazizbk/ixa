/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-02 09:37:23
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeNewPageLayoutController', [
'$log', '$scope', '$rootScope','FormsBag', 'FrontofficePageLayoutFactory', '$state', '$stateParams', 'NotificationService', '$timeout',
function($log, $scope, $rootScope, FormsBag, FrontofficePageLayoutFactory, $state, $stateParams, NotificationService, $timeout) {
    $log = $log.getInstance('FrontofficeNewPageLayoutController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.forms.data = {
        page_layout: {
        }
    };

    $scope.formLocale = $rootScope.locale;

    $scope.addPageLayout = function(pageLayoutData) {
        return FrontofficePageLayoutFactory.createPageLayout(pageLayoutData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.page_layout.formController) {
                $scope.forms.params.page_layout.formController.$setPristine();
            }

            $state.go('backoffice.frontoffice.page_layouts_list');

            NotificationService.addSuccess(Translator.trans('notification.success.page_layout.create'));

            // clear form error messages
            delete $scope.forms.errors.page_layout;
        }, function(response) {
            $log.error('Create page layout failed', response);
            NotificationService.addError(Translator.trans('notification.error.page_layout.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.page_layout = response.data.errors;
            }
        });

    }

    $scope.formPageLayoutTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_page_layout_form');

    $scope.forms.params = {
        page_layout: {
            submitActive: true,
            submitLabel: Translator.trans('create.page_layout'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.addPageLayout($scope.forms.data.page_layout);
            },
            cancelAction: function() {
                $state.go('backoffice.frontoffice.page_layouts_list');
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('page_layout.has.not.been.saved.are.you.sure.you.want.to.continue')
        }
    }
}]);
