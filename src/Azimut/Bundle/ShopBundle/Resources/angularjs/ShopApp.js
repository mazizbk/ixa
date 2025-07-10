/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:41:28
 */

'use strict';

//we declare submodules to be sure all is instanciated when calling app config function
angular.module('azimutShop.controller', []);
angular.module('azimutShop.directive', []);
angular.module('azimutShop.service', []);
angular.module('azimutShop.filter', []);

angular.module('azimutShop', [
    'azimutBackoffice',
    'azimutShop.controller',
    'azimutShop.directive',
    'azimutShop.service',
    'azimutShop.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider',
function($stateProvider) {
    $stateProvider
        //main state
        .state('backoffice.shop', {
            url: "/shop",
            templateUrl: Routing.generate('azimut_shop_backoffice_jsview_main'),
            resolve: {
                // initialise your entity factories data here
                demoFactoryInitPromise: function(ShopOrderFactory){
                    return ShopOrderFactory.init();
                }
            },
            controller: 'ShopMainController'
        })

        .state('backoffice.shop.order_list', {
            url: '/orders',
            templateUrl: Routing.generate('azimut_shop_backoffice_jsview_order_list'),
            controller: 'ShopOrderListController'
        })

        .state('backoffice.shop.order_detail', {
            url: '/orders/order_:order_id',
            templateUrl: Routing.generate('azimut_shop_backoffice_jsview_order_detail'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.shop.order_detail';
                }
            },
            controller: 'ShopOrderDetailController'
        })
    ;
}])

.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {
    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('shop.app.name'),
        icon: 'glyphicon-pro glyphicon-pro-shopping-bag',
        stateName: 'backoffice.shop',
        displayOrder: 50
    });
}]);

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutShop');
