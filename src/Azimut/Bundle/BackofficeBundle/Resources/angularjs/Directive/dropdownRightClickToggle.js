/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:40:03
 *
 ******************************************************************************
 *
 * this is an implementation of ui.bootstrap.dropdownToggle, but at right click intead of left
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('dropdownRightClickToggle', [
'$document', '$location',
function ($document, $location) {
    var openElement = null;
    var closeMenu = angular.noop;
    return {
        restrict: 'CA',
        link: function(scope, element, attrs) {
            scope.$watch('$location.path', function() { closeMenu(); });
            element.parent().bind('click', function() { closeMenu(); });
            element.bind('contextmenu', function (event) {

                var menuElement = element.find('.context-menu');

                var elementWasOpen = (element === openElement);

                event.preventDefault();
                event.stopPropagation();

                if (!!openElement) {
                    closeMenu();
                }

                if (!elementWasOpen) {
                    menuElement.addClass('opened');
                    openElement = menuElement;

                    //Position the menu at mouse cursor position
                    //CAUTION : this requires jQuery
                    menuElement.css( "left", event.pageX-element.offset().left+"px" );
                    menuElement.css( "top", event.pageY-element.offset().top+"px" );

                    closeMenu = function (event) {
                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        $document.unbind('click', closeMenu);
                        menuElement.removeClass('opened');
                        closeMenu = angular.noop;
                        openElement = null;
                    };
                    $document.bind('click', closeMenu);
                }
            });
        }
    };
}])

;