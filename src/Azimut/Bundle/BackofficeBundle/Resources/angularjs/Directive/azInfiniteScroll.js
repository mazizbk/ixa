/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:23:30
 *
 ******************************************************************************
 *
 * Active infinite scroll on a div element with overflow scroll
 * When scrolling is near to the bottom, then the function passed in param is called
 * Ex : <div az-infinite-scroll="displayMoreMedias">...</div>
 * Optional param : distance to the bottom at wich the function should be called
 * Ex : az-infinite-scroll-distance="200" (in pixels)
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azInfiniteScroll', [
'$log',
function($log) {

    $log = $log.getInstance('azInfiniteScroll');

    return {
        link: function(scope, element, attrs) {

            //check required params
            if(!attrs.azInfiniteScroll) {
                $log.warn("No action has been attached to infinite scroll of element ",element);
                return;
            }

            var scrollDistance = 20; // in px

            if (attrs.azInfiniteScrollDistance != null) {
              scope.$watch(attrs.azInfiniteScrollDistance, function(value) {
                return scrollDistance = value;
              });
            }

            var loadAction = null;
            scope.$watch(attrs.azInfiniteScroll, function(value) {
                return loadAction = value;
            });

            element.on('scroll',function() {

                if(this.scrollTop + this.clientHeight > this.scrollHeight - scrollDistance) {
                    loadAction();
                    scope.$apply();
                }
            });

        }
    }
}]);
