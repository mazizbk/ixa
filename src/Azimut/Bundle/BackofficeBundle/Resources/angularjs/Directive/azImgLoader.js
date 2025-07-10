/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-09 14:59:55
 *
 ******************************************************************************
 *
 * Add "img-loading" class on image until it's fully loaded.
 * The class "img-loading-error" will be added instead in case of error.
 * A boolean scope attribute can be passed as parameter, it will be set when
 * image is loaded.
 *
 * Usage :
 *
 *     <img ng-src="....." az-img-loader />
 *     <img ng-src="....." az-img-loader="imageLoaded" />
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azImgLoader', [
'$parse', '$log',
function($parse, $log) {

    $log = $log.getInstance('azImgLoader');

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {

            var modelParser = $parse(attrs.azImgLoader);

            element.addClass('img-loading');

            element.on('load', function() {
                scope.$parent.$apply(function() {
                    modelParser.assign(scope.$parent, true);
                    element.removeClass('img-loading');
                });
            });
            element.on('error', function() {
                scope.$parent.$apply(function() {
                    modelParser.assign(scope.$parent, false);
                    element.removeClass('img-loading');
                    element.addClass('img-loading-error');
                });
            });
        }
    };

}]);
