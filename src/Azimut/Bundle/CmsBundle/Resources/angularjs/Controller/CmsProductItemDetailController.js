/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 11:39:10
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsProductItemDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'CmsProductItemFactory', '$state', '$stateParams', 'NotificationService', '$timeout', '$templateCache', 'baseStateName',
function($log, $scope, $rootScope, FormsBag, CmsProductItemFactory, $state, $stateParams, NotificationService, $timeout, $templateCache, baseStateName) {
    $log = $log.getInstance('CmsProductItemDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.forms = new FormsBag();

    $scope.breadcrumb = {
        elements: []
    };

    $scope.showBreadcrumb = true;
    $scope.baseStateName = baseStateName;

    $scope.stateGoBack = function() {
        $state.go($scope.baseStateName + '.product_item_list');
    };

    CmsProductItemFactory.getProductItem($stateParams.product_item_id, 'all').then(function(response) {
        var productItem = response.data.productItem;

        $scope.formProductItemTemplateUrl = Routing.generate('azimut_cms_backoffice_jsview_product_item_form', { action: 'update' });
        // $templateCache.remove($scope.formProductItemTemplateUrl);

        $scope.productItem = productItem;
        $scope.forms.data.product_item = angular.copy(productItem);
        $scope.forms.data.product_item.price = $scope.forms.data.product_item.decimalPrice;

        $scope.forms.params.product_item = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return CmsProductItemFactory.updateProductItem($scope.forms.data.product_item).then(function(response) {
                    $log.info('Product item has been updated', response);

                    // remove dirty state on form
                    if (undefined != $scope.forms.params.product_item.formController) {
                        $scope.forms.params.product_item.formController.$setPristine();
                    }
                    $scope.stateGoBack();
                    NotificationService.addSuccess(Translator.trans('notification.success.product.item.update'));
                }, function(response) {
                    NotificationService.addError(Translator.trans('notification.error.product.item.update'), response);

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

        $scope.mainContentLoaded();
    }, function(response) {
        NotificationService.addCriticalError(Translator.trans('notification.error.product.item.%id%.get', { 'id' : $stateParams.product_item_id }));

        $scope.$parent.showContentView = false;
        return;
    });
}]);
