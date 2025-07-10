/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 14:13:31
  *
  ******************************************************************************
  *
  * Format price stored as cents
  *
  */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('azShopPrice', function() {
    return function(price) {
        price = parseFloat(price ? price / 100 : 0).toFixed(2).replace('.', ','); // @TODO use localized money format
        return price + ' €';
    };
})
;
