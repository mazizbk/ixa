/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:34:45
 *
 ******************************************************************************
 *
 * Set an element as draggable
 * It is intended to work with the drop directive
 * The directive has a required attribute content, it is the dragged data
 *
 * drag-style attribute can optionnaly be set to specify a class to add when dragging
 * drag-type attribute can be set to define the type of data (to match the drop-accept-type of drop directive)
 *
 * Usage :
 *     <span drag="myDataJsObject" drag-style="myDragCssClass" drag-type="my-data-type"></span>
 *
 * Drop zone example :
 *     <span drop="myDropZoneData" drop-style="myDropCssClass" drop-accept-type="my-data-type"></span>
 *
 * See drop directive for more informations
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('drag', [
'$rootScope', '$parse',
function($rootScope, $parse) {

    function dragStart(evt, element, dragStyle) {
        element.addClass(dragStyle);
        evt.originalEvent.dataTransfer.setData("id", evt.target.id); // this is required for Firefox
        evt.originalEvent.dataTransfer.effectAllowed = 'move';
        evt.originalEvent.dataTransfer.dropEffect = 'move';

    };
    function dragEnd(evt, element, dragStyle) {
        element.removeClass(dragStyle);
    };

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {
            var dragData = $parse(attrs.drag)(scope);

            //set element as draggable only if dragData is set (drag="dragdata")
            if (!dragData) {
                throw "missing drag directive parameter";
            }

            var dragStyle = attrs.dragStyle;
            var dragType = attrs.dragType;

            attrs.$set('draggable', 'true');

            element.bind('dragstart', function(evt) {
                //$rootScope.draggingElement = element;
                $rootScope.draggingElementData = dragData;
                $rootScope.draggingElementType = dragType;
                dragStart(evt, element, dragStyle);
            });
            element.bind('dragend', function(evt) {
                dragEnd(evt, element, dragStyle);
            });
        }
    }
}]);
