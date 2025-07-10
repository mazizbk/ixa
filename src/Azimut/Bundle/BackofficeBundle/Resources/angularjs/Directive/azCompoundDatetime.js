/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-31 10:54:08
 *
 ******************************************************************************
 *
 * Bind serialized datetime (string) to multiple html select field representing the date
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azCompoundDatetime', [
'$parse',
function($parse) {
    return {
        restrict: 'A',
        link: {
            pre: function(scope, element, attrs) {
                var modelGetter = $parse(attrs.azCompoundDatetime);
                var modelSetter = modelGetter.assign;

                scope.$watch(attrs.azCompoundDatetime, function(newValue, oldValue) {
                    if(!angular.isString(newValue)) return;

                    var modelDate = new Date(modelGetter(scope));

                    var newModel = {
                        date: {
                            year: modelDate.getFullYear().toString(),
                            month: (modelDate.getMonth()+1).toString(),
                            day: modelDate.getDate().toString()
                        },
                        time: {
                            hour: modelDate.getHours().toString(),
                            minute: modelDate.getMinutes().toString()
                        }
                    };

                    modelSetter(scope, newModel);

                });
            }
        }
    }
}]);
