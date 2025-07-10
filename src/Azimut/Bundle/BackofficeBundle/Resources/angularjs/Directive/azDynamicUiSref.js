/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-05-02 16:51:53
 *
 ******************************************************************************
 *
 * add support for var binding to ui-router ui-sref
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azDynamicUiSref', [
'$state',
function($state){
    return {
        restrict: 'A',

        link: function(scope, element, attrs) {
            var stateName = scope.$eval(attrs.azDynamicUiSref);

            //element.attr('ui-sref', stateName);
            element.bind('click',function() {
                $state.go(stateName);
            });
        }
    }
}]);
