/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:52:16
 *
 ******************************************************************************
 *
 * transform an object to a formData object that can contain files to upload
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('formDataObject', function() {
    return function(data) {

        //WARNING : IE9 doesn't support FormData, neither File
        var formDataObj = new FormData();

        //this flatten a nested object to an array of path => value (except for File object)
        //we have a multi nested object data and we want a flat object without subobjects
        function formatArrayData(data, keyPrefix, formatedData) {
            if(!formatedData) var formatedData = {};
            for (var prop in data) {
                var key = prop;
                if(keyPrefix) key = keyPrefix+"["+prop+"]";
                if(data[prop] != null && typeof data[prop] == 'object' && data[prop].constructor != File) formatArrayData(data[prop],key,formatedData);
                else formatedData[key] = data[prop];
              }
              return formatedData;
        }
        data = formatArrayData(data);

        //convert data to a FormData object
        angular.forEach(data, function(value, key) {
            formDataObj.append(key, value);
        });

        return formDataObj;
    };
});
