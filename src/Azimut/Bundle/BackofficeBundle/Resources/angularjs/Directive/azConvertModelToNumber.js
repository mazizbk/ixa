/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-17 12:23:38
 *
 ******************************************************************************
 *
 * This directive is made for select form inputs wich hold numerical values
 *
 * It solves the following issue:
 * By default if our model contains a numeric value of 42, the select option
 * with value "42" (evaluated as a string) will not be considered selected.
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azConvertModelToNumber', function() {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {

            ngModel.$parsers.push(function(value) {
                return isNaN(value)? value : parseInt(value, 10);
            });

            ngModel.$formatters.push(function(value) {
                return '' + value;
            });

        }
    };
});
