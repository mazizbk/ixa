/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-19 14:12:28
 *
 ******************************************************************************
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azCompoundRadios', [
'$log', '$compile', '$parse', 'ArrayExtra',
function($log, $compile, $parse, ArrayExtra) {

    $log = $log.getInstance('azCompoundRadios');

    function link(scope, element, attrs) {

        // compile for binding our new local ng-model
        $compile(element)(scope);

        var modelParser = $parse(attrs.azCompoundRadios);

        scope.$watchCollection(attrs.azCompoundRadios, function(newValue) {
            // if model item is object, replace object by its id
            if (angular.isObject(newValue)) {
                newValue = ''+newValue.id;
            }
            modelParser.assign(scope, newValue);

        });
    }

    return {
        restrict: 'A',
        scope: true, // isolate scope
        compile: function(element, attrs) {

            // add a model attached to the scope to represent the state of the radio
            element.attr('ng-model', element.attr('az-compound-radios'));

            // store target model name before removing the attr
            element.attr('az-compound-radios-model', element.attr('az-compound-radios'));

            // prevent from compile recursions
            element.removeAttr('az-compound-radios');

            // TODO: check validity of params

            // redirect to link function
            return link;
        }
    }
}]);
