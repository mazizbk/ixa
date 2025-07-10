/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-23 15:47:27
 *
 ******************************************************************************
 *
 * Get only uniques values in an array
 * This is usefull for avoiding ngRepeat:dupes errors
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('unique', function() {
    return function(inputArray) {

        if(undefined === inputArray) return;

        return inputArray.filter(function(item, pos) {
            return inputArray.indexOf(item) == pos;
        });

    };
});
