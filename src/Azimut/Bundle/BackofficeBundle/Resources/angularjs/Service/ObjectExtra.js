/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-02 11:40:19
 *
 ******************************************************************************
 *
 * global utility functions for Objects
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('ObjectExtra', function() {

    /**
     * Create a complete deep copy of an object
     * Each object property is copied and each subobject is recursively cloned into
     * a new object using the same function.
     *
     * Usage: deepCopy(source, [destination])
     *
     * Return: new object or destination
     **/
    function deepCopy(source, destination) {
        if(!destination) destination = {};

        for(var property in source) {
            if(null !== source[property] && "object" === typeof source[property]) {
                if(!destination[property]) {
                    if(angular.isArray(source[property])) destination[property] = [];
                    else destination[property] = {};
                }

                deepCopy(source[property], destination[property]);
            }
            else {
                destination[property] = source[property];
            }
        }

        return destination;
    }

    /**
     * Deeply iterate throw an object
     * and delete all undefined properties
     **/
    function deleteUndefinedProperties(object) {
        for(var property in object) {
            if(undefined === object[property]) delete object[property];
            else if("object" === typeof object[property]) {
                deleteUndefinedProperties(object[property]);
            }
        }

        return object;
    }

    /**
     * Clear all properties of an object
     *
     * Usage: clear(object)
     **/
    function clear(object) {
        for(var property in object) {
            delete object[property];
        }
    }

    /**
     * Create a shalow copy of an object
     * Destination object is first emptied
     * Each object property is copied without recursivity and without cloning
     *
     * Usage: shallowCopy(source, [destination])
     *
     * Return: new object or destination
     **/
    function shallowCopy(source, destination) {
        if(!destination) destination = {};

        clear(destination);

        for(var property in source) {
            destination[property] = source[property];
        }

        return destination;
    }

    /**
     * Check if an object has no properties
     */
    function isEmpty(object) {
        for(var property in object) {
            return false;
        }
        return true;
    }

    function flattenKeys(obj) {
        var ret = [];
        for(var i in obj) {
            ret.push(i);
            if(obj[i] instanceof Object){
                ret = ret.concat(flattenKeys(obj[i]));
            }
        }
        return ret;
    }

    /**
     * Count number of properties in an object
     */
    function length(obj) {
        return Object.keys(obj).length;
    }

    return {
        deepCopy: deepCopy,
        deleteUndefinedProperties: deleteUndefinedProperties,
        clear: clear,
        shallowCopy: shallowCopy,
        isEmpty: isEmpty,
        flattenKeys: flattenKeys,
        length: length
    }
});
