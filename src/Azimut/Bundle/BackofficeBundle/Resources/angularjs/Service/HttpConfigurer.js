/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:44:31
 *
 ******************************************************************************
 *
 * service made for setting global options of $http service
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('HttpConfigurer', [
'$http',
function ($http) {

    return {
/*
        //Tells angularjs to use www form urlencoded by default on POST commands
        setWwwFormUrlencodedContentType: function() {

            // Use x-www-form-urlencoded Content-Type
            $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

            // Override $http service's default transformRequest
            $http.defaults.transformRequest = [function(data) {

                //The workhorse; converts an object to x-www-form-urlencoded serialization.
                var param = function(obj) {
                    var query = '';
                    var name, value, fullSubName, subName, subValue, innerObj, i;

                    for(name in obj) {
                        value = obj[name];

                        if(value instanceof Array) {
                            for(i=0; i<value.length; ++i){
                                subValue = value[i];
                                fullSubName = name + '[' + i + ']';
                                innerObj = {};
                                innerObj[fullSubName] = subValue;
                                query += param(innerObj) + '&';
                            }
                        }
                        else if(value instanceof Object) {
                            for(subName in value) {
                                subValue = value[subName];
                                fullSubName = name + '[' + subName + ']';
                                innerObj = {};
                                innerObj[fullSubName] = subValue;
                                query += param(innerObj) + '&';
                            }
                        }
                        else if(value !== undefined && value !== null) {
                            query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
                        }
                    }
                    return query.length ? query.substr(0, query.length - 1) : query;
                };

                return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
            }];
        }*/
    }

}])

;