/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:50:37
 ******************************************************************************
 *
 * This directive is made for a wrapper element wich we don't know the width
 * containing many elements with the same knowed width.
 * On attaching this directive on each child element, the margins will be
 * redistributed to make all elements well centered
 * An optionnal second attribute can be set to update margins on a
 * specific event: az-redistribute-margins-from-parent-width-update-event="my_event_name"
 *
 */

'use strict';

angular.module('azimutMediacenter.directive')

.directive('azRedistributeMarginsFromParentWidth', function() {

    function redistributeMarginsFromParentWidth(element,newWidth) {
        var parentElement = element.parent();

        var parentElementWidth = element.parent().width();
        var elementWidth = element.width();
        if(newWidth) elementWidth = newWidth;
        var elementWidthWithMargins = elementWidth + 10; //minimal margin

        //determine how many item per ligne it should have
        var elementsPerLignes = Math.floor(parentElementWidth/elementWidthWithMargins);

        //calculate spacing
        var spacing = Math.floor((parentElementWidth - elementWidth * elementsPerLignes) / (elementsPerLignes*2));

        //apply new margins
        element.css('margin-left',spacing+'px','important');
        element.css('margin-right',spacing+'px','important');
    }

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {

            var eventUpdate = attrs['azRedistributeMarginsFromParentWidthUpdateEvent'];

            redistributeMarginsFromParentWidth(element);

            $(window).resize(function() {
                redistributeMarginsFromParentWidth(element);
            });

            //if eventUpdate name has been provided, plug it
            if(eventUpdate) {
                scope.$on(eventUpdate, function(event,data) {
                    redistributeMarginsFromParentWidth(element,data.width);
                });
            }
        }
    }
})

;