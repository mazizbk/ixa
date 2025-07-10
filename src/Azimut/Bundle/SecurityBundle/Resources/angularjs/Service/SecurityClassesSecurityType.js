/**
 * Created by mikaelp on 3/8/2016.
 */

(function () {
    'use strict';
    angular.module('azimutSecurity.service').factory('SecurityClassesSecurityType', [
        '$http', '$q', 'SecurityClassesParent',
        function ($http, $q, SecurityClassesParent) {

            var data = null;
            var loadingPromise = null;
            var urlPrefix = 'azimut_security_api_';

            return {
                getSecurityType: function (className) {
                    var promise;
                    if(loadingPromise) {
                        promise = loadingPromise.then(function() {
                            return SecurityClassesParent.getParent(className).then(function(className){
                                return data[className];
                            });
                        })
                    }
                    else if (data) {
                        return SecurityClassesParent.getParent(className).then(function(className){
                            return data[className];
                        });
                    }
                    else {
                        loadingPromise = promise = $http.get(Routing.generate(urlPrefix + 'get_classes_security_type')).then(function(result){
                            data = result.data;
                            loadingPromise = null;
                            return SecurityClassesParent.getParent(className).then(function(className){
                                return data[className];
                            });
                        });
                    }

                    return promise;
                }
            }
        }
    ]);
})();