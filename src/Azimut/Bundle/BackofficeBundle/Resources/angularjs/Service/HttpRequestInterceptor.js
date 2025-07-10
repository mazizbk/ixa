/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-23 11:30:35
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('HttpRequestInterceptor', [
'ObjectExtra', '$window', '$log', '$q',
function(ObjectExtra, $window, $log, $q) {
    $log = $log.getInstance('HttpRequestInterceptor');

    var pendingRedirection = false;

    var interceptor = {
        request: function(config) {
            // cancel request if pending redirection
            if (pendingRedirection) {
                var canceler = $q.defer();
                config.timeout = canceler.promise;
                canceler.resolve();
            }

            // intercept data sent to server, and remove all undefined properties
            if(null !== config.data && "object" === typeof config.data) {
                config.data = ObjectExtra.deleteUndefinedProperties(config.data);
            }

            return config;
        },

        'responseError': function(rejection) {
            // if not authenticated, redirect user to login page
            if (!pendingRedirection && rejection.status == 401) {
                pendingRedirection = true;
                $log.info('User session lost, redirecting to login');
                $window.location = Routing.generate('azimut_security_login');
            }

            return $q.reject(rejection);
        }
    };

    return interceptor;
}]);
