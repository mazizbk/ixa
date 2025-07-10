/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-24 16:43:08
 */

'use strict';

angular.module('azimutMediacenter.filter')

.filter('azFileSize', function() {
    return function(size, baseUnit) {
        var unitPrefix = '';

        if (size > 1000) {
            size = size / 1000;
            unitPrefix = 'K';
        }
        if (size > 1000) {
            size = size / 1000;
            unitPrefix = 'M';
        }
        if (size > 1000) {
            size = size / 1000;
            unitPrefix = 'G';
        }
        if (size > 1000) {
            size = size / 1000;
            unitPrefix = 'T';
        }

        return size.toFixed(2) + unitPrefix + baseUnit;
    };
});
