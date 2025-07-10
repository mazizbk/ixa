/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-08 16:11:22
 *
 ******************************************************************************
 *
 * capitalize a string
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('capitalize', function() {
    return function(input) {
        if(undefined == input) return;
        return input.charAt(0).toUpperCase() + input.slice(1);
    };
});
