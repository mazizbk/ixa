/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:43:35
 *
 ******************************************************************************
 *
 * Service for natural sorting datas
 *
 * Forked from https://bitbucket.org/OverZealous/angularjs-naturalsort
 * ex : will sort 'myvalue1', 'myvalue2', 'myvalue11' instead of 'myvalue1', 'myvalue11', 'myvalue2'
 *
 * Usage :
 *
 *     ng-repeat="obj in objs | orderBy: natural('name')"
 *     ng-repeat="obj in objs | orderBy: natural('id'):true"
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('NaturalSorting', [
'$locale',
function($locale) {

    // amount of extra zeros to padd for sorting
    var padding = function(value) {
        return "00000000000000000000".slice(value.length);
    },

    // Converts a value to a string.  Null and undefined are converted to ''
    toString = function(value) {
        if(value === null || value === undefined) return '';
        return ''+value;
    },

    // Fix numbers to be correctly padded
    fixNumbers = function(value) {
        // First, look for anything in the form of d.d or d.d.d...
        return toString(value).replace(/(\d+)((\.\d+)+)?/g, function ($0, integer, decimal, $3) {
            // If there's more than 2 sets of numbers...
            if (decimal !== $3) {
                // treat as a series of integers, like versioning,
                // rather than a decimal
                return $0.replace(/(\d+)/g, function ($d) {
                    return padding($d) + $d;
                });
            } else {
                // add a decimal if necessary to ensure decimal sorting
                decimal = decimal || ".0";
                return padding(integer) + integer + decimal + padding(decimal);
            }
        });
    },

    // Calculate the default out-of-order date format (dd/MM/yyyy vs MM/dd/yyyy)
    natDateMonthFirst = $locale.DATETIME_FORMATS.shortDate.charAt(0) === "M",
    // Replaces all suspected dates with a standardized yyyy-m-d, which is fixed below
    fixDates = function(value) {
        // first look for dd?-dd?-dddd, where "-" can be one of "-", "/", or "."
        return toString(value).replace(/(\d\d?)[-\/\.](\d\d?)[-\/\.](\d{4})/, function($0, $m, $d, $y) {
            // temporary holder for swapping below
            var t = $d;
            // if the month is not first, we'll swap month and day...
            if(!natDateMonthFirst) {
                // ...but only if the day value is under 13.
                if(Number($d) < 13) {
                    $d = $m;
                    $m = t;
                }
            } else if(Number($m) > 12) {
                // Otherwise, we might still swap the values if the month value is currently over 12.
                $d = $m;
                $m = t;
            }
            // return a standardized format.
            return $y+"-"+$m+"-"+$d;
        });
    };

    // The actual object used by this service
    return {
        naturalValue: fixNumbers,
        naturalDateValue: fixDates,
        naturalSort: function(a, b) {
            a = fixNumbers(a);
            b = fixNumbers(b);
            return (a < b) ? -1 : ((a > b) ? 1 : 0);
        }
    };
}]);
