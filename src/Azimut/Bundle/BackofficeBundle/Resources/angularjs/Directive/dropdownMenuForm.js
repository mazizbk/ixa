/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:39:39
 *
 ******************************************************************************
 *
 * Add-on to twitter bootstrap
 * class dropdown-menu-form to allow displaying forms into bootstrap dropdown menus
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('dropdownMenuForm',function() {
    return {
        restrict: 'C',
        link: function(scope, element, attrs) {
            element.on('click',function(evt) {
                evt.stopPropagation();
            });
        }
    }
})

;