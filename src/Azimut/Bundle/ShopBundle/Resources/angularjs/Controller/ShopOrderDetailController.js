/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 10:59:53
 */

'use strict';

angular.module('azimutShop.controller')

.controller('ShopOrderDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'ShopOrderFactory', '$state', '$stateParams', 'NotificationService', '$timeout', 'ShopDeliveryTrackingFactory',
function($log, $scope, $rootScope, FormsBag, ShopOrderFactory, $state, $stateParams, NotificationService, $timeout, ShopDeliveryTrackingFactory) {
    $log = $log.getInstance('ShopOrderDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    var order = ShopOrderFactory.findOrder($stateParams.order_id);
    $scope.orderStatuses = ShopOrderFactory.availableStatuses();

    if (!order) {
        NotificationService.addCriticalError(Translator.trans('notification.error.order.%id%.get', { 'id' : $stateParams.order_id }));
        $scope.$parent.showContentView = false;
        return;
    }

    $scope.order = order;

    $scope.formOrderTemplateUrl = Routing.generate('azimut_shop_backoffice_jsview_order_form');

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    $scope.stateGoBack = function(id) {
        $state.go('backoffice.shop.order_list');
    };

    //Fetch the complete version of the order, with all fields
    ShopOrderFactory.getOrder(order.id).then(function(response) {
        var order = angular.copy(response.data.order);
        $scope.order = order;

        // we don't use the real Order object because we need raw data to be binded into the form
        $scope.forms.data.order = {
            id: order.id,
            deliveryTrackings: order.deliveryTrackings,
            privateComment: order.privateComment,
            status: order.status,
            paymentDate: order.paymentDate
        };

        $scope.forms.params.order = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.saveOrder($scope.forms.data.order);
            },
            cancelAction: function() {
                $scope.stateGoBack(order.id);
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('order.has.not.been.saved.are.you.sure.you.want.to.continue')
        };

        $scope.mainContentLoaded();

        // Get updated data for delivery tracking informations
        // (Calls an API on backoffice wich is a proxy for the real provider API)
        if (0 < order.deliveryTrackings.length) {
            var deliveryTrackingIndexes = [];
            for (var i=0; i < order.deliveryTrackings.length; i++) {
                deliveryTrackingIndexes[order.deliveryTrackings[i].id] = i; // Store array position by ids (i will be uncorrect in then function because of async call)
                if (false == order.deliveryTrackings[i].isDelivered) {
                    ShopDeliveryTrackingFactory.getDeliveryTrackingUpdate(order.deliveryTrackings[i].id).then(function(data) {
                        order.deliveryTrackings[deliveryTrackingIndexes[data.deliveryTracking.id]] = data.deliveryTracking;
                    });
                }
            };
        }
    });

    $scope.saveOrder = function(orderData) {
        return ShopOrderFactory.updateOrder(orderData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.order.formController) {
                $scope.forms.params.order.formController.$setPristine();
            }

            $scope.stateGoBack(order.id);
            NotificationService.addSuccess(Translator.trans('notification.success.order.update'));

            // clear form error messages
            delete $scope.forms.errors.order;
        }, function(response) {
            $log.error('Update order failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.order.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.order = response.data.errors;
            }
        });
    };
}]);
