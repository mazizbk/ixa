/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-29 13:21:31
 *
 ******************************************************************************
 *
 * This directive removes the draggable HTML attribute of an element
 * when the boolean passed as directive attribute is true
 *
 * This has been made to solve a Firefox bug. See issue #243 in GitLab and
 * Mozilla bugtracker : https://bugzilla.mozilla.org/show_bug.cgi?id=800050
 *
 * Example usage :
 *     <div az-drag="currentFile" az-drag-cancel="currentFile.editMode" />
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azDragCancel', function() {
    return {
        link: function(scope, element, attrs) {
            scope.$watch(attrs.azDragCancel, function(value) {
                if(value === true) {
                    // store original draggable value
                    attrs.$set('originalDraggableValue', attrs.draggable);

                    // remove draggable attribute
                    if(null != attrs.draggable) attrs.$set('draggable', 'false');
                }
                else {
                    // restore original draggable value
                    if(null != attrs.originalDraggableValue) attrs.$set('draggable', attrs.originalDraggableValue);
                }
            });
        }
    };
});
