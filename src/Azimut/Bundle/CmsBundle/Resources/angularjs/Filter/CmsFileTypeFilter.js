/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-04 12:30:17
 */


'use strict';

angular.module('azimutBackoffice.filter')

.filter('cmsFileTypeFilter', function() {
    return function(items, types) {

        // if types is empty then do not filter
        if(types.length == 0) return items;

        var filtered = [];

        angular.forEach(items, function(item) {
            for(var i=0; i<types.length; i++) {
                if(item.cmsFileType == types[i]) {
                    filtered.push(item);
                    break;
                }
            }
        });

        return filtered;
    };
})

;