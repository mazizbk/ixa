/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:33:49
 *
 ******************************************************************************
 *
 * add a event on file input change to update an array of files (filesdata) in the calling scope
 * added because angularjs doesn't bind file input type !
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azFileInput',[
'$parse',
function($parse){
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            scope = scope.$parent;

            var filesdataGetter = $parse(attrs.azFileInput);
            var filesdataSetter = filesdataGetter.assign;

            scope.$watch(attrs.azFileInput, function (newValue) {
                // allow only set of null value, input file element only accept filename
                if (null == newValue) {
                    element.val(null);
                }
            });

            element.bind('change',function(event) {

                filesdataSetter(scope, []);

                // WARNING : event.target.files will be null in IE9, input fields cannot be manipulated
                // solution is to submit form into an hidden iframe, see controllers

                for(var i in event.target.files) {
                    // add only objects (browsers can add functions that we don't want)
                    if(typeof event.target.files[i] == 'object') {

                        var filesdata = filesdataGetter(scope);
                        filesdata.push(event.target.files[i]);
                        filesdataSetter(scope,filesdata);
                    }
                }
            });
        }
    }
}]);
