/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-24 10:54:29
 *
 ******************************************************************************
 *
 * Addon to YlcJsMap to support Angular bindings
 *
 * Usage :
 *
 *    <az-map-point-picker
 *        class="ylc-js-map"
 *        data-map-svg-src="img/maps/map.svg"
 *        data-map-svg-native-width="1920"
 *        data-map-svg-native-height="1080"
 *        data-map-initial-zoom="1"
 *        data-map-max-zoom="4"
 *        data-map-min-zoom="1"
 *        data-map-edit-x-id="myXInputId"
 *        data-map-edit-y-id="myYInputId"
 *    ></az-map-point-picker>
 *
 *    <input type="text" name="x" id="myXInputId" />
 *    <input type="text" name="y" id="myYInputId" />
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azMapPointPicker',[
'$parse', '$log',
function($parse, $log) {
    return {
        restrict: 'AE',
        link: function(scope, element, attrs) {
            var xInput = $('#' + attrs['mapEditXId']);
            var yInput = $('#' + attrs['mapEditYId']);

            var xModel = xInput.attr('ng-model');
            var yModel = yInput.attr('ng-model');

            var xModelGetter = $parse(xModel);
            var xModelSetter = xModelGetter.assign;
            var yModelGetter = $parse(yModel);
            var yModelSetter = yModelGetter.assign;

            // xInput.on('propertychange'... => doesn't work
            // dblclick is the event used inside js map to position marker
            // alternative will be to trigger a custom event on drag end inside js map
            element.on('dblclick', function(evt) {
                xModelSetter(scope, xInput.val());
                yModelSetter(scope, yInput.val());
            });
        }
    }
}]);
