/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 09:43:03
 */

'use strict';

angular.module('azimutShop.controller')

.controller('ShopOrderListController', [
'$log', '$scope', '$rootScope', 'ShopOrderFactory', '$state', '$stateParams', 'NotificationService', 'DataSortDefinitionBuilder',
function($log, $scope, $rootScope, ShopOrderFactory, $state, $stateParams, NotificationService, DataSortDefinitionBuilder) {
    $log = $log.getInstance('ShopOrderListController');

    $scope.$parent.showContentView = true;

    $scope.orders = ShopOrderFactory.orders();

    $scope.orderStatuses = ShopOrderFactory.availableStatuses();

    $scope.ordersSortDefinitionBuilder = new DataSortDefinitionBuilder('shop-orders', [
        // {
        //     'label': Translator.trans('creation.date'),
        //     'property': 'id',
        //     'reverse': true,
        //     'default': true
        // },
        {
            'label': Translator.trans('number'),
            'property': 'number',
            'reverse': true,
            'default': true
        },
        {
            'label': Translator.trans('status'),
            'property': 'status',
            'reverse': true
        },
        {
            'label': Translator.trans('order.date'),
            'property': 'orderDate',
            'reverse': true
        },
        {
            'label': Translator.trans('total.amount') + ' ' + Translator.trans('excl.vat'),
            'property': 'totalPreTaxAmount',
            'reverse': true
        },
        {
            'label': Translator.trans('total.amount') + ' ' + Translator.trans('incl.vat'),
            'property': 'totalAmount',
            'reverse': true
        }
    ]);

    $scope.openOrder = function(order) {
        $state.go('backoffice.shop.order_detail', {order_id: order.id});
    };

    $scope.deleteOrder = function(order) {
        ShopOrderFactory.deleteOrder(order).then(function (response) {
            $log.info('Order has been deleted', response);
            NotificationService.addSuccess(Translator.trans('notification.success.order.delete'));
        }, function(response) {
            $log.error('Error while deleting order', response);
            NotificationService.addError(Translator.trans('notification.error.order.delete'), response);
        });
    };

    $scope.searchStatus = '' + ShopOrderFactory.defaultFilterStatus();
}]);
