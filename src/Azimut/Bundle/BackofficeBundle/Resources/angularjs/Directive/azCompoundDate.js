/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-10-07 10:50:00
 *
 ******************************************************************************
 *
 * Bind serialized date (string) to multiple html select field representing the date
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azCompoundDate', [
'$parse',
function($parse) {
    return {
        restrict: 'A',
        priority: 1,
        link: {
            pre: function(scope, element, attrs) {
                var modelGetter = $parse(attrs.azCompoundDate);
                var modelSetter = modelGetter.assign;

                scope.$watch(attrs.azCompoundDate, function(newValue, oldValue) {
                    if(!angular.isString(newValue)) return;

                    var modelDate = new Date(modelGetter(scope));

                    var newModel = {
                        year: modelDate.getFullYear().toString(),
                        month: (modelDate.getMonth()+1).toString(),
                        day: modelDate.getDate().toString()
                    };

                    modelSetter(scope, newModel);

                });
            }
        }
    }
}]);
