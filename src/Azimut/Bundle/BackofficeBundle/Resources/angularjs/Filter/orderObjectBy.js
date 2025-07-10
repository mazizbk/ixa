/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:42:01
 *
 ******************************************************************************
 *
 * order objects array by an object property
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('orderObjectBy', function() {
    return function(items, field, reverse) {
        var filtered = [];
        angular.forEach(items, function(item) {
            filtered.push(item);
        });
        filtered.sort(function (a, b) {
            return (a[field] > b[field]);
        });
        if(reverse) filtered.reverse();
        return filtered;
    };
});
