/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:50:28
 *
 ******************************************************************************
 *
 * global utility functions for Functions
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('FunctionExtra', function() {
    return {
        //test if a js object is a function
        isFunction: function(functionToCheck) {
            var getType = {};
            return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
        }
    }
});
