/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:32:54
 *
 ******************************************************************************
 *
 * Make an html element resizable by mouse dragging a handle
 * The parameter of the directive defined if we want to store and recall the panel width
 *
 * Usage:
 *     <div class="side-panel" az-resizable-panel="true"></div>
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azResizablePanel', [
'$log', '$rootScope', '$timeout',
function($log, $rootScope, $timeout) {

    $log = $log.getInstance('azResizablePanel');

    var minResizeWidth = 100;
    var enableStorage = false;

    function resizePanel(resizeTargetElement, newWidth) {

        // set min panel size when toggled on (to prevent panel showing too narrow)
        if(newWidth < minResizeWidth) newWidth = minResizeWidth;

        resizeTargetElement.css('width',newWidth+'px');

        // block if parent is overflowing
        var parentElt = resizeTargetElement.parent();
        var overflow = parentElt[0].scrollWidth-parentElt.innerWidth();
        if( overflow > 0 ) resizeTargetElement.css('width', newWidth-overflow+'px');

        if(enableStorage) {
            // store new element width
            var resizeWidth = parseInt(resizeTargetElement.css('width'));
            localStorage.setItem('az-resizable-panel-'+resizeTargetElement.attr('id')+'-width', resizeWidth);
        }

        // dispatch custom interfacePanelResize event
        $rootScope.$broadcast('interfacePanelResize');
    }

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {

            if(undefined != attrs['azResizablePanel']) {
                if(!attrs['id']) {
                    $log.warn('az-resizable-panel directive needs an id attribute to be able to store panel width');
                }
                else {
                    enableStorage = true;
                }
            }

            if(enableStorage) {
                // restore element state
                var storedWidth = localStorage.getItem('az-resizable-panel-'+element.attr('id')+'-width');
                if(storedWidth) {
                    resizePanel(element, storedWidth);
                }
            }

            element.find('.resizable-border').mousedown(function(evt) {

                // the active border element we've clicked on
                var borderElement = $(this);

                element.removeClass('transition-active');

                // this is the half of the active border width
                // (will be used to ajust the edge position on the mouse cursor during moving )
                var offset = Math.round(borderElement.width()/2);

                // x offset of the element, relative to window
                var elementX = element.offset().left;

                var elementWidth = element.width();

                var resizeMousemoveEventListener = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.cancelBubble = true;

                    // in some browser, we can't prevent text from being selected during drag, so we fix it with css
                    $('body').addClass('unselectable');

                    var mouseX = event.pageX;

                    var panelWidth;
                    if(!borderElement.hasClass('resizable-border-left')) {
                        panelWidth = mouseX-elementX-offset;
                    }
                    else {
                        panelWidth = (elementX+elementWidth)-mouseX-offset;
                    }

                    resizePanel(element, panelWidth);
                }

                var resizeMouseupEventListener = function() {
                    window.removeEventListener('mousemove', resizeMousemoveEventListener);
                    window.removeEventListener('mouseup', resizeMouseupEventListener);
                    $('body').removeClass('unselectable');
                }

                window.addEventListener('mousemove', resizeMousemoveEventListener);

                window.addEventListener('mouseup', resizeMouseupEventListener);
            });
        }

    }

}]);
