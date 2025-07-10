/**
 * Created by mikaelp on 2/26/2016.
 */

(function () {
    'use strict';
    angular.module('azimutSecurity.service').factory('SecurityClassesHierarchy', [
        '$http', '$q', '$log', 'ArrayExtra', 'ObjectExtra',
        function ($http, $q, $log, ArrayExtra, ObjectExtra) {
            $log = $log.getInstance('SecurityClassesHierarchy');

            var hierarchy = null;
            var flattenParents = {};
            var flattenChildren = {};
            var urlPrefix = 'azimut_security_api_';

            var loadingPromise = null;

            function loadHierarchy() {
                loadingPromise = $http.get(Routing.generate(urlPrefix + 'get_roles_hierarchy')).then(function (response) {
                    hierarchy = response.data;
                    ArrayExtra.arrayWalkRecursive(hierarchy, function (children, className, previousClassesNames) {
                        if (flattenChildren.hasOwnProperty(className)) {
                            flattenChildren[className] = flattenChildren[className].concat(ObjectExtra.flattenKeys(children));
                        }
                        else {
                            flattenChildren[className] = ObjectExtra.flattenKeys(children);
                        }
                        flattenChildren[className] = ArrayExtra.uniqueValues(flattenChildren[className]);

                        flattenParents[className] = previousClassesNames;
                        flattenParents[className].splice(flattenParents[className].indexOf(className), 1);
                        flattenParents[className] = ArrayExtra.uniqueValues(flattenParents[className]);
                    });
                }, function (response) {
                    $log.error(response);
                });

                return loadingPromise;
            }

            return {
                getHierarchy: function () {
                    var promise;
                    if (hierarchy) {
                        var deferred = $q.defer();
                        deferred.resolve(hierarchy);
                        promise = deferred.promise;
                    }
                    else if(loadingPromise) {
                        promise = loadingPromise;
                    }
                    else {
                        promise = loadHierarchy();
                    }

                    return promise;
                },
                hasData: function () {
                    return !!hierarchy;
                },
                invalidate: function () {
                    hierarchy = null;
                    flattenChildren = {};
                    flattenParents = {};
                },
                getClassChildren: function (className) {
                    var deferred = $q.defer();

                    if (hierarchy) {
                        deferred.resolve(flattenChildren[className]);
                    }
                    else if(loadingPromise) {
                        loadingPromise.then(function(){
                            deferred.resolve(flattenChildren[className]);
                        })
                    }
                    else {
                        loadHierarchy().then(function () {
                            deferred.resolve(flattenChildren[className]);
                        });
                    }

                    return deferred.promise;
                },
                getClassParents: function (className) {
                    var deferred = $q.defer();

                    if (hierarchy) {
                        deferred.resolve(flattenParents[className]);
                    }
                    else if(loadingPromise) {
                        loadingPromise.then(function(){
                            deferred.resolve(flattenParents[className]);
                        })
                    }
                    else {
                        loadHierarchy().then(function () {
                            deferred.resolve(flattenParents[className]);
                        });
                    }

                    return deferred.promise;
                },
                isChildren: function (baseClass, parentClass) {
                    var deferred = $q.defer();

                    if (hierarchy) {
                        deferred.resolve(flattenChildren[baseClass].indexOf(parentClass)>-1);
                    }
                    else {
                        loadHierarchy().then(function () {
                            deferred.resolve(flattenChildren[baseClass].indexOf(parentClass)>-1);
                        });
                    }

                    return deferred.promise;
                },
                isParent: function (baseClass, parentClass) {
                    var deferred = $q.defer();

                    if (hierarchy) {
                        deferred.resolve(flattenParents[baseClass].indexOf(parentClass)>-1);
                    }
                    else {
                        loadHierarchy().then(function () {
                            deferred.resolve(flattenParents[baseClass].indexOf(parentClass)>-1);
                        });
                    }

                    return deferred.promise;
                }
            }
        }
    ]);
})();
