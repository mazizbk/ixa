/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 11:16:34
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsProductItemListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'CmsProductItemFactory', 'baseStateName',
function($log, $scope, $state, $stateParams, NotificationService, CmsProductItemFactory, baseStateName) {
    $log = $log.getInstance('CmsProductItemListController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.baseStateName = baseStateName;

    CmsProductItemFactory.getProductItems($stateParams.file_id).then(function(response) {
        $scope.productItems = response.data.productItems;
        $scope.mainContentLoaded();
    });

    $scope.openProductItem = function(productItem) {
        $state.go($scope.baseStateName + '.product_item_detail', { product_item_id: productItem.id });
    };

    $scope.openNewProductItem = function() {
        $state.go($scope.baseStateName + '.product_item_new');
    };

    $scope.deleteProductItem = function(productItem) {
        CmsProductItemFactory.deleteProductItem(productItem).then(function (response) {
            $log.info('Product item has been deleted', response);
            $scope.productItems.splice($scope.productItems.indexOf(productItem), 1);
            NotificationService.addSuccess(Translator.trans('notification.success.product.item.delete'));
        }, function(response) {
            $log.error('Error while deleting productItem', response);
            NotificationService.addError(Translator.trans('notification.error.product.item.delete'), response);
        });
    };

    $scope.visibleFilter = true;
}]);
