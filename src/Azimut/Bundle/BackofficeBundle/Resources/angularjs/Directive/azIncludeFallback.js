/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-11 10:37:13
 *
 ******************************************************************************
 *
 * To be used paired with ngInclude directive.
 * Fallback to another template if the one provided in ngInclude can't be found
 *
 * Listens to httpTemplateError event
 *
 * This directive nedds HttpRequestTemplateInterceptor service and azHttpTemplateError
 * directive.
 *
 * Usage :
 *     <div ng-include="layoutPreviewUrl" az-include-fallback="layoutDefaultPreviewUrl"></div>
 *
 * How does it works ?
 * -------------------
 *
 * HttpRequestTemplateInterceptor service intercept the HTTP error and inject
 * "az-http-template-error" html tag in DOM.
 *
 * Then the directive azHttpTemplateError is called, which emit an httpTemplateError
 * event
 *
 * Finally the event is catched by azIncludeFallback directive, which can make
 * a call to an alternative template
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azIncludeFallback',[
'$log', '$http', '$templateCache', '$compile',
function($log, $http, $templateCache, $compile) {

    $log = $log.getInstance('azIncludeFallback');

    return {
        restrict: 'A',
        link: {
            pre: function preLink(scope, element, attrs) {

                if (undefined == attrs['azIncludeFallback']) {
                    $log.warn('No fallback template url provided');
                    return;
                }

                var fallbackTemplate = scope.$eval(attrs['azIncludeFallback']);

                // when received event, include the fallback template
                scope.$on('templateError', function(e, data) {
                    // add information on scope, in case we wan't to do something on parent controllers
                    scope.templateError = true;
                    scope.templateErrorUrl = data.url;

                    // include the provided fallback template
                    $http.get(fallbackTemplate, {cache: $templateCache}).success(function(response) {
                        var contents = $('<div/>').html(response).contents();
                        element.html(contents);
                        $compile(contents)(scope);
                    });
                });
            }
        }
    }
}]);
