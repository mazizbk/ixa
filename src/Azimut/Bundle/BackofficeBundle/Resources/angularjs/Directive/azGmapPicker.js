/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-20 11:58:39
 *
 ******************************************************************************
 *
 * Display a Google Map to manipulate latitude and longitude input fields
 *
 * Usage :
 *
 *    <az-gmap-picker
 *        id='my-gmap-picker-id'
 *        latitude-id="myLatitudeInputId"
 *        longitude-id="myLongitudeInputId"
 *        zoom="7"
 *    >
 *        <input type="text" name="latitude" id="myLatitudeInputId" />
 *        <input type="text" name="longitude" id="myLongitudeInputId" />
 *    </az-gmap-picker>
 *
 * Note : input fields can be inside or outside the az-gmap-picker element
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azGmapPicker',[
'$parse', '$log',
function($parse, $log) {
    // if no coordinates, display marker on Paris
    var defaultLat = 48.856614;
    var defaultLng = 2.3522219;

    var link = function(scope, element, attrs) {
        var apiKeyParam = (scope.gmap_api_key)? '&key=' + scope.gmap_api_key : '';
        $.getScript('https://www.google.com/jsapi', function() {
            google.load('maps', '3', { other_params:  apiKeyParam, callback: function() {
                initMap(scope, element, attrs);
            }});
        });
    };

    var initMap = function(scope, element, attrs) {
        var mapCanvasElement = document.getElementById(attrs['id']+'-canvas');

        var latInput = document.getElementById(attrs['latitudeId']);
        var longInput = document.getElementById(attrs['longitudeId']);

        var latModel = latInput.getAttribute('ng-model');
        var lngModel = longInput.getAttribute('ng-model');
        var latModelGetter = $parse(latModel);
        var latModelSetter = latModelGetter.assign;
        var lngModelGetter = $parse(lngModel);
        var lngModelSetter = lngModelGetter.assign;

        var latValue = latModelGetter(scope);
        var lngValue = lngModelGetter(scope);

        var coordinates = new google.maps.LatLng(latValue?latValue:defaultLat, lngValue?lngValue:defaultLng);

        // build Gmap instance
        var map = new google.maps.Map(mapCanvasElement, {
            zoom: parseInt(attrs['zoom']),
            center: coordinates
        });

        // place draggable marker
        var marker = new google.maps.Marker({
            draggable: true,
            position: coordinates,
            map: map,
            title: "Drag me"
        });

        // update input fields when marker dragged
        google.maps.event.addListener(marker, 'dragend', function (event) {
            scope.$apply(function() {
                latModelSetter(scope, event.latLng.lat());
                lngModelSetter(scope, event.latLng.lng());
            });
        });

        // update marker on input fields value change
        var modelChangeHandler = function(valueName, newValue) {

            // if user is entering a negative value, wait for it
            if('-' == newValue) return;

            // check format value if not a number
            if (newValue && isNaN(newValue) && '' != newValue) {
                // convert comma to point
                newValue = newValue.replace(',', '.');

                // add decimals if not specified
                if(-1 == newValue.indexOf('.')) newValue= newValue + '.0';

                // check format
                var latLngComponentRegex = /\-?\d+(\.\d+)/;
                if(!latLngComponentRegex.test(newValue)) newValue = 0;
            }

            if ('lat' == valueName) {
                latValue = newValue;
                latModelSetter(scope, newValue);
            }
            else {
                lngValue = newValue;
                lngModelSetter(scope, newValue);
            }

            var coordinates = new google.maps.LatLng(latValue?latValue:defaultLat, lngValue?lngValue:defaultLng);
            marker.setPosition(coordinates);
            map.setCenter(marker.getPosition());
        }

        // watch lat and lng model changes
        scope.$watch(latModel, function(newLatValue) {
            modelChangeHandler('lat', newLatValue);
        });

        scope.$watch(lngModel, function(newLngValue) {
            modelChangeHandler('lng', newLngValue);
        });
    };

    return {
        restrict: 'E',
        transclude: true,
        compile: function(element, attrs) {
            // set gmap canvas id at compilation so it is available when gmap needs it
            element.find('.gmap-canvas').attr('id', attrs['id']+'-canvas');

            return link;
        },

        template: '<div ng-transclude></div><div class="gmap-canvas" style="height: 300px;margin: 0;padding: 0;"></div>'
    }
}]);
