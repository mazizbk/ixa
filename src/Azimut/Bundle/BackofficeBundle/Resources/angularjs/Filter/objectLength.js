/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-27 09:41:36
 *
 ******************************************************************************
 *
 * Count number of properties in an object
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('objectLength', [
'ObjectExtra',
function(ObjectExtra) {
    return function(obj) {
        if (null == obj) {
            return;
        }
        if (!angular.isObject(obj)) {
            throw 'Filter "objectLength" cannot be used on a non object';
        }

        return ObjectExtra.length(obj);
    };
}]);
