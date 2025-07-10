/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-11 10:48:31
 *
 ******************************************************************************
 *
 * Intercept HTTP request to an AngularJS template
 *
 * Warning: this is done by assuming all templates are called from a URL
 * containing "/jsviews/"
 *
 * This service is made to provide support for azIncludeFallback directive
 *
 *
 * Registering interceptor
 * -----------------------
 *
 * angular.module('myModule').config(function($httpProvider) {
 *     $httpProvider.interceptors.push('HttpRequestTemplateInterceptor');
 * });
 *
 *
 * How does it works ?
 * -------------------
 *
 * This service intercept the HTTP error and inject "az-http-template-error"
 * html tag in DOM.
 *
 * Then the directive azHttpTemplateError is called, which emit an httpTemplateError
 * event
 * Finally the event is catched by azIncludeFallback directive, which can make
 * a call to an alternative template
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('HttpRequestTemplateInterceptor', [
'$log', '$q',
function($log, $q) {

    $log = $log.getInstance('HttpRequestTemplateInterceptor');

    var interceptor = {
        'responseError': function(rejection) {
            // if it is a template (url containing "/jsviews/")
            if (!!rejection.config.url.match(/^.*\/jsviews\//g)) {
                $log.warn('Angular template not found at "' + rejection.config.url + '"');

                // inject call for httpTemplateError directive
                rejection.data = '<az-http-template-error url="\''+ rejection.config.url + '\'" data-error-message="HttpRequestTemplateInterceptor: fetch template error"></az-http-template-error>';
                return rejection;
            }
            else {
                return $q.reject(rejection);
            }
        }
    };

    return interceptor;
}]);
