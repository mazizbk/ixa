/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 11:33:05
 */

'use strict';

angular.module('azimutCms.service')

.factory('CmsProductItemFactory', [
'$log', '$http', '$q',
function($log, $http, $q) {
    $log = $log.getInstance('CmsProductItemFactory');

    var factory = this;
    factory.urlPrefix = 'azimut_cms_api_';

    return {
        getProductItems: function(cmsFileId) {
            var routeParams = null;
            if (null != cmsFileId) {
                routeParams = { cmsFileId: cmsFileId };
            }
            return $http.get(Routing.generate(factory.urlPrefix + 'get_productitems', routeParams));
        },

        createProductItem: function(productItemData) {
            return $http.post(Routing.generate(factory.urlPrefix + 'post_productitems'), { product_item: productItemData });
        },

        getProductItem: function(id, locale) {
            var routeParams = {
                id: id,
                locale: locale
            };
            return $http.get(Routing.generate(factory.urlPrefix + 'get_productitem', routeParams));
        },

        updateProductItem: function(productItemData) {
            var productItemApiData = angular.copy(productItemData);

            var productItemId = productItemApiData.id;
            delete productItemApiData.id;
            delete productItemApiData.createdAt;
            delete productItemApiData.cmsFile;
            delete productItemApiData.decimalPrice;

            return $http.put(Routing.generate(factory.urlPrefix + 'put_productitem', { id: productItemId }), { product_item: productItemApiData });
        },

        deleteProductItem: function(productItem) {
            return $http.delete(Routing.generate(factory.urlPrefix + 'delete_productitem', { id: productItem.id }));
        }
    }
}]);
