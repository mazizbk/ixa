/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:41:18
 *
 ******************************************************************************
 *
 * limitTo filter exist in Angular, this is the equivalent for start
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
});
