/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:28:01
 *
 ******************************************************************************
 *
 * include a partial without creating a new scope
 *
 */

/*'use strict';

angular.module('azimutBackoffice.directive')

.directive('staticInclude', function($http, $templateCache, $compile) {
    return function(scope, element, attrs) {
        var templatePath = attrs.staticInclude;

        $http.get(templatePath, {cache: $templateCache}).success(function(response) {
            var contents = $('<div/>').html(response).contents();
            element.html(contents);
            $compile(contents)(scope);
        });
    };
});
*/
