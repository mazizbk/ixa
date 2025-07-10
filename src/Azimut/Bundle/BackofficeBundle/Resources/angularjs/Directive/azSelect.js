/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-10-25 11:42:20
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azSelect', [
'$log', '$parse',
function($log, $parse) {
    $log = $log.getInstance('azSelect');

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs) {
            var modelParser = $parse(attrs.ngModel);

            if (!attrs.multiple) {
                scope.$watch(attrs.ngModel, function(newValue) {
                    if (undefined == newValue) newValue = '';

                    // if model item is object, replace object by its id
                    if (angular.isObject(newValue)) {
                        newValue = ''+newValue.id;
                    }
                    else {
                        newValue = ''+newValue;
                    }
                    modelParser.assign(scope, newValue);
                });
            }
            else {
                scope.$watchCollection(attrs.ngModel, function(newValue) {
                    if (undefined == newValue) newValue = [];

                    for (var i = newValue.length - 1; i >= 0; i--) {
                        // if model item is object, replace object by its id
                        if (angular.isObject(newValue[i])) {
                            newValue[i] = ''+newValue[i].id;
                        }
                        else {
                            newValue[i] = ''+newValue[i];
                        }
                    }
                });
            }
        }
    }
}]);
