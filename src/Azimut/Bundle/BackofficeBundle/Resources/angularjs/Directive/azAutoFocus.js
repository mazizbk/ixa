/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:38:41
 *
 ******************************************************************************
 *
 * Autofocus the field holding the directive based on the boolean model given
 * as parameter
 *
 * Usage :
 *     <input type="text" az-auto-focus="my.model.boolean.value" />
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azAutoFocus', [
'$timeout',
function($timeout) {
    return {
        link: function(scope, element, attrs) {
            scope.$watch(attrs.azAutoFocus, function(value) {
                if(value === true) {
                    $timeout(function() {
                        element[0].focus();
                        element[0].select();
                        scope[attrs.azAutoFocus] = false;
                    });
                }
            });
        }
    };
}]);
