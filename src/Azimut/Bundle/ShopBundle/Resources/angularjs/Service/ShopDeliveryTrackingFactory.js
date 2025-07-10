/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-13 12:09:55
 */

'use strict';

angular.module('azimutShop.service')

.factory('ShopDeliveryTrackingFactory', [
'$log', '$http',
function($log, $http) {
    $log = $log.getInstance('ShopDeliveryTrackingFactory');

    var factory = this;

    factory.urlPrefix = 'azimut_shop_api_';

    return {
        getDeliveryTrackingUpdate: function(id) {
            return $http.get(Routing.generate(factory.urlPrefix + 'get_deliverytrackingupdate', {id: id})).then(function (response) {
                return response.data;
            });
        },
    }
}]);
