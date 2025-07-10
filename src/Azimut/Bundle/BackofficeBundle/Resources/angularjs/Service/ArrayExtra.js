/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:51:17
 *
 ******************************************************************************
 *
 * global utility functions for Arrays
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('ArrayExtra', [
'$log', 'FunctionExtra',
function($log, FunctionExtra) {

    $log = $log.getInstance('ArrayExtra');

    function arrayWalkRecursive(arr, callback, previousValues)
    {
        if(!previousValues){
            previousValues = [];
        }
        for(var i in arr){
            var pathPreviousValues = previousValues.slice();
            pathPreviousValues.push(i);
            callback(arr[i], i, pathPreviousValues.slice());
            arrayWalkRecursive(arr[i], callback, pathPreviousValues);
        }
    }

    return {
        /**
         * Returns the first element in array having ALL the specified values in attrs
         */
        findFirstInArray: function (array, attrs) {
            if (!angular.isArray(array)) {
                throw '[ArrayExtra] findFirstInArray: Expected first argument to be of type array.';
            }
            if (!angular.isObject(attrs)) {
                throw '[ArrayExtra] findFirstInArray: Expected second argument to be of type object. For simple value search, use native Array.indexOf() method.';
            }

            for (var i = 0, len = array.length; i < len; i++) {
                var nbMatchingAttrs = 0;

                for (var key in attrs) {
                    //if the attribute is a function then run it and get the returned value
                    if(FunctionExtra.isFunction(array[i][key])) {
                        var attrValue = array[i][key]();
                    }
                    else {
                        var attrValue = array[i][key];
                    }

                    if (attrValue == attrs[key]) {
                        nbMatchingAttrs ++;
                    }

                    if(nbMatchingAttrs == Object.keys(attrs).length) return array[i];
                }
            }
            return null;
        },
        /**
         * Returns the all elements in array having ALL the specified values in attrs
         */
        findInArray: function (array, attrs) {
            if (!angular.isArray(array)) {
                throw '[ArrayExtra] findInArray: Expected first argument to be of type array.';
            }
            if (!angular.isObject(attrs)) {
                throw '[ArrayExtra] findInArray: Expected second argument to be of type object. For simple value search, use native Array.indexOf() method.';
            }

            var results = [];
            for (var i = 0, len = array.length; i < len; i++) {
                var nbMatchingAttrs = 0;

                for (var key in attrs) {
                    //if the attribute is a function then run it and get the returned value
                    if(FunctionExtra.isFunction(array[i][key])) {
                        var attrValue = array[i][key]();
                    }
                    else {
                        var attrValue = array[i][key];
                    }

                    if (attrValue == attrs[key]) {
                        nbMatchingAttrs ++;
                    }

                }
                //add object to results only if matching all attributes
                if (nbMatchingAttrs == Object.keys(attrs).length) results.push(array[i]);
            }
            return results;
        },
        merge: function (array1, array2) {
            if (undefined == array2) return array1;

            for (var i=0; i<array2.length; i++) {
                if (-1 === array1.indexOf(array2[i])) array1.push(array2[i]);
            }
            return array1;
        },
        arrayWalkRecursive: arrayWalkRecursive,
        uniqueValues: function(array){
            var a = array.concat();
            for(var i=0; i<a.length; ++i) {
                for(var j=i+1; j<a.length; ++j) {
                    if(a[i] === a[j])
                        a.splice(j--, 1);
                }
            }
            return a;
        }
    }
}]);
