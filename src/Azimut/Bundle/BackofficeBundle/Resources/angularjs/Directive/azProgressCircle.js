/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-07 17:33:59
 *
 ******************************************************************************
 *
 * Circular progress bar
 *
 * The model parameter must containt a value from 0 to 100
 *
 * Usage :
 *     <az-progress-circle ng-model="currentFile.progress"></az-progress-circle>
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azProgressCircle',
function() {
    return {
        restrict: 'E',
        score: true,
        link: function(scope, element, attrs) {

            scope.circleProgressPercent = 0;
            scope.circleProgress = -258;

            scope.$watch(attrs.ngModel, function(newValue) {
                scope.circleProgressPercent = newValue;
                scope.circleProgress = Math.round(newValue*258/100-258);
            });

        },
        template:   '<div class="progress-circle">'+
                    '    <svg version="1.1" x="0px" y="0px" width="100%" height="100%" viewBox="0 0 90 90">'+
                    '       <path fill="none"'+
                    '            stroke-dashoffset="{{ circleProgress }}"'+
                    '            stroke-dasharray="258 258"'+
                    '            stroke-width="8"'+
                    '            d="M45,4C22.392,4,4,22.393,4,45c0,22.608,18.392,41,41,41s41-18.392,41-41S67.608,4,45,4z"/>'+
                    '   </svg>'+
                    '   <span>{{ circleProgressPercent }}%</span>'+
                    '</div>'
    }
});
