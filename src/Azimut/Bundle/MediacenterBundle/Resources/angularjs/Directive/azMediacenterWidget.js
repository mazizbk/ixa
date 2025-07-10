/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-05-28 16:00:43
 */

'use strict';

angular.module('azimutMediacenter.directive')

.directive('azMediacenterWidget', function() {
    return {
        restrict: 'E',
        transclude: true,
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_widget'),
        link: function(scope,element,attr) {
            element.find('.close').bind('click',function() {
                $('#azimutMediacenterWidget').hide();
            })
        }
    }
});
