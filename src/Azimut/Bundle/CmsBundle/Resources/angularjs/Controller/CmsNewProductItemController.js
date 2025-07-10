/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 11:24:32
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsNewProductItemController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsProductItemFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache', 'baseStateName',
function($log, $scope, $rootScope, FormsBag, CmsProductItemFactory, $state, $stateParams, NotificationService, $timeout, $templateCache, baseStateName) {
    $log = $log.getInstance('CmsNewProductItemController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;
    $scope.baseStateName = baseStateName;

    $scope.stateGoBack = function() {
        $state.go($scope.baseStateName + '.product_item_list');
    };

    $scope.formProductItemTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_product_item_form', { action: 'create' });
    // $templateCache.remove($scope.formProductItemTemplateUrl);

    $scope.forms.data.product_item = {
        'cmsFile': $stateParams.file_id,
    };

    $scope.forms.params.product_item = {
        submitActive: true,
        submitLabel: Translator.trans('create'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            CmsProductItemFactory.createProductItem($scope.forms.data.product_item).then(function(response) {
                $log.info('Product item has been created', response);

                // remove dirty state on form
                if (undefined != $scope.forms.params.product_item.formController) {
                    $scope.forms.params.product_item.formController.$setPristine();
                }
                $scope.stateGoBack();
                NotificationService.addSuccess(Translator.trans('notification.success.product.item.create'));
            }, function(response) {
                NotificationService.addError(Translator.trans('notification.error.product.item.create'), response);

                // display form error messages
                if(undefined != response.data.errors) {
                    $scope.forms.errors.product_item = response.data.errors;
                }
            });
        },
        cancelAction: function() {
            $scope.stateGoBack();
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('product_item.has.not.been.saved.are.you.sure.you.want.to.continue')
    };
}]);
