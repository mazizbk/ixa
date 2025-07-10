/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-22 14:22:26
 *
 ******************************************************************************
 *
 * Collapses all collapsable panels on element click
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azMenuBtn', [
'$log',
function($log) {
    $log = $log.getInstance('azMenuBtn');

    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            // check required params
            if (!attrs.azMenuBtn) {
                $log.warn("Menu button has undefined target id", element);
                return;
            }

            element.click(function() {
                var target = $('#'+attrs.azMenuBtn);

                if (target.hasClass('collapsed')) {
                    // collapse side panels on narrow screens
                    if ($(window).width() < 768) {
                        var panels = $('.collapsable-panel');
                        panels.addClass('transition-active');
                        panels.addClass('collapsed');
                        $('.collapsable-panel .collapsable-border-btn').addClass('collapsed');
                    }
                }

                target.addClass('transition-active');
                target.toggleClass('collapsed');
            });
        }
    }
}]);
