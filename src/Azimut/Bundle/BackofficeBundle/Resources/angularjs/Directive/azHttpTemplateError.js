/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-11 12:39:12
 *
 ******************************************************************************
 *
 * Emit an httpTemplateError event when called
 *
 * This directive, paired with HttpRequestTemplateInterceptor service is made to
 * provide support for azIncludeFallback directive
 *
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

angular.module('azimutBackoffice.service')

.directive('azHttpTemplateError', function() {
    return {
        restrict: 'E',
        scope: {
            'url': '='
        },
        link: function(scope) {
            scope.$emit('templateError', {url: scope.url});
        }
    };
});
