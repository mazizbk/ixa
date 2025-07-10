/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 09:30:42
 */

'use strict';

angular.module('azimutShop.service')

.factory('ShopOrderFactory', [
'$log', '$http', '$rootScope', 'formDataObject', 'ArrayExtra', '$q', 'ObjectExtra', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $rootScope, formDataObject, ArrayExtra, $q, ObjectExtra, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('ShopOrderFactory');

    var factory = this;
    factory.refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_shop_api_';

    factory.isGrantedUser = false;

    factory.orders = [];
    factory.ordersIndex = [];

    factory.availableStatuses = null;
    factory.defaultFilterStatus = null;

    /*** privates functions ***/

    factory.getAvailableStatusesFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_orders_availablestatuses')).then(function (response) {
            factory.availableStatuses = response.data.statuses;
            factory.defaultFilterStatus = response.data.defaultFilterStatus;
        });
        return promise;
    };

    factory.getOrdersFromServer = function() {
        var routeParams = null;

        var promise = $http.get(Routing.generate(factory.urlPrefix + 'get_order', routeParams)).then(function(response) {
            // Clear orders array object
            factory.orders.splice(0);

            // Reset index
            factory.ordersIndex = [];

            for (var i=0; i<response.data.orders.length; i++) {
                factory.orders[i] = response.data.orders[i];
                // Update index
                factory.ordersIndex[factory.orders[i].id] = factory.orders[i];
            }

            return factory.orders;
        });

        return promise;
    };

    factory.refreshCache = function() {
        return factory.getOrdersFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    };

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // Do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || 0 != $state.current.name.indexOf('backoffice.shop') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh');

        return factory.refreshCache();
    };

    /*** end privates functions ***/


    /*** public functions ***/

    return {
        // Init service (constructor)
        init: function() {
            var deferred = $q.defer();

            // If factory is already initialized, do not wait for data and refresh in background
            if(factory.initialized) {
                factory.getAvailableStatusesFromServer();
                factory.refreshCache();
                deferred.resolve();
            }
            else {
                factory.getAvailableStatusesFromServer().then(function(response) {
                    factory.refreshCache().then(function(response) {
                        factory.isGrantedUser = true;
                        factory.initialized = true;

                        // Schedule auto cache refresh
                        $interval.cancel(factory.refreshIntervalPromise);
                        factory.refreshIntervalPromise = $interval(function() {
                            factory.autoRefreshCache();
                        }, factory.autoCacheRefreshDelay * 60 * 1000);

                        deferred.resolve();
                    }, function(response) {
                        // If api access is forbidden or unauthorized
                        if(401 == response.data.error.code || 403 == response.data.error.code) {
                            factory.isGrantedUser = false;
                            // Resolve instead of reject, instead this will be blocking, we want the controller to be called all the time so we can handle a redirect
                            deferred.resolve();
                        }
                        else {
                            deferred.reject(response);
                        }
                    });
                }, function(response) {
                    // if api access is forbidden or unauthorized
                    if(401 == response.data.error.code || 403 == response.data.error.code) {
                        factory.isGrantedUser = false;
                        // resolve instead of reject, instead this will be blocking, we want the controller to be called all the time so we can handle a redirect
                        deferred.resolve();
                    }
                    else {
                        deferred.reject(response);
                    }
                });
            }

            return deferred.promise;
        },

        refreshCache: function() {
            factory.refreshCache();
        },

        orders: function(type){
            return factory.orders
        },

        availableStatuses:  function() {
            return factory.availableStatuses;
        },

        defaultFilterStatus:  function() {
            return factory.defaultFilterStatus;
        },

        findOrder: function(id) {
            return factory.ordersIndex[id];
        },

        getOrder: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;

            var promise = $http.get(Routing.generate(factory.urlPrefix + 'get_order', {id: id}) + '?locale='+locale).then(function(response) {
                var order = response.data.order;

                if(undefined != factory.ordersIndex[order.id]) {
                    // Update order index
                    factory.ordersIndex[order.id].status = order.status;
                }

                return response;
            });

            return promise;
        },

        updateOrder: function(order) {
            // Keep only tracking code in delivery trackings form data
            var deliveryTrackingsFormData = [];
            for (var i=0; i < order.deliveryTrackings.length; i++) {
                deliveryTrackingsFormData[i] = {
                      code: order.deliveryTrackings[i].code,
                };
            };

            // Work on a copy of order
            var orderPut = {
                deliveryTrackings: deliveryTrackingsFormData,
                privateComment: order.privateComment,
                status: order.status,
                paymentDate: order.paymentDate
            }

            var promise = $http.put(Routing.generate(factory.urlPrefix + 'put_order',{ id: order.id }), {order: orderPut}).then(function(response) {
                var order = response.data.order;

                if(undefined != factory.ordersIndex[order.id]) {
                    // Update order index
                    factory.ordersIndex[order.id].status = order.status;
                }

                return response;
            });

            return promise;
        },

        deleteOrder: function(order) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix + 'delete_order',{ id: order.id })).then(function(response) {
                // Unlink order and let Garbage Collector destroy it
                factory.orders.splice(factory.orders.indexOf(order),1);
                delete factory.ordersIndex[order.id];
            });

            return promise;
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        }
    }

    /*** end public functions ***/

}]);
