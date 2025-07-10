/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:36:11
 *
 ******************************************************************************
 *
 * set an element as droppable (a draggable can be droped on it)
 * It is intended to work with the drag directive
 * The directive has a required attribute content, it is the dropzone data
 *
 * drop-style attribute can optionnaly be set to specify a class to add when hoverring drop zone
 * drop-type attribute can be set to define the type of data (to match the drag-type of drag directive)
 *
 * The directive broadcast an 'dropEvent' event on the root scope, containing both the dragged and drop zone data
 *
 * Usage :
 *     <span drop="myDropZoneData" drop-style="myDropCssClass" drop-accept-type="my-data-type"></span>
 *
 * Drop zone example :
 *     <span drag="myDataJsObject" drag-style="myDragCssClass" drag-type="my-data-type"></span>
 *
 * Listener example in controller :
 *     $scope.$on('dropEvent', function(evt, dragged, dropped, droppedFiles) {
 *         // ...
 *     });
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('drop', [
'$rootScope',
function($rootScope) {

    function dragEnter(evt, element, dropStyle) {
        evt.preventDefault();
        evt.stopPropagation();
        element.addClass(dropStyle);
    };
    function dragLeave(evt, element, dropStyle) {
        evt.preventDefault();
        evt.stopPropagation();
        element.removeClass(dropStyle);
    };
    function dragOver(evt, element, dropStyle) {
        evt.preventDefault();
        evt.stopPropagation();
        //reapply the style when over, because overing children cause a call to dragLeave
        element.addClass(dropStyle);

    };
    function drop(evt, element, dropStyle) {
        evt.preventDefault();
        evt.stopPropagation();
        element.removeClass(dropStyle);
    };

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {
            var dropData = scope[attrs.drop];
            var dropStyle = attrs.dropStyle;
            var dropAcceptType = attrs.dropAcceptType;

            scope.$watch(attrs.drop, function(newValue) {
                dropData = newValue;
            });

            element.bind('dragenter', function(evt) {
                dragEnter(evt, element, dropStyle);
            });
            element.bind('dragleave', function(evt) {
                dragLeave(evt, element, dropStyle);
            });
            element.bind('dragover', function(evt) {
                // if dragged element type is not accepted by drop zone
                if(dropAcceptType && $rootScope.draggingElementType != dropAcceptType) {
                    evt.originalEvent.dataTransfer.effectAllowed = 'none';
                    evt.originalEvent.dataTransfer.dropEffect = 'none';
                }
                dragOver(evt, element, dropStyle);
            });
            element.bind('drop', function(evt) {
                drop(evt, element, dropStyle);
                var files = null;
                if(evt.originalEvent.dataTransfer && evt.originalEvent.dataTransfer.files) files = evt.originalEvent.dataTransfer.files;
                $rootScope.$broadcast('dropEvent', $rootScope.draggingElementData, dropData, files);

                // end of drag'n drop, clear data
                $rootScope.draggingElementData = null;
                $rootScope.draggingElementType = null;
            });
        }
    }
}])

;