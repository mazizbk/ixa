/**
 * Created by mikaelp on 3/8/2016.
 */

(function () {
    'use strict';
    angular.module('azimutSecurity.service').factory('SecurityClassesParent', [
        '$http', '$q',
        function ($http, $q) {

            var data = null;
            var loadingPromise = null;
            var urlPrefix = 'azimut_security_api_';

            return {
                getParent: function (className) {
                    if(className.indexOf('Proxies\\__CG__\\')===0) {
                        className = className.substr(15);
                    }
                    var promise;
                    if(loadingPromise) {
                        promise = loadingPromise.then(function() {
                            return data[className]?data[className]:className;
                        })
                    }
                    else if (data) {
                        var deferred = $q.defer();
                        deferred.resolve(data[className]?data[className]:className);
                        promise = deferred.promise;
                    }
                    else {
                        loadingPromise = promise = $http.get(Routing.generate(urlPrefix + 'get_classes_parent')).then(function(result){
                            data = result.data;
                            loadingPromise = null;
                            return data[className]?data[className]:className;
                        });
                    }

                    return promise;
                }
            }
        }
    ]);
})();
