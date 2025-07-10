/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-01 17:39:52
 */

'use strict';

angular.module('azimutCms.directive')

.directive('azCmsSelectWidget', function() {
    return {
        restrict: 'E',
        transclude: true,
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_widget_select_file'),
        link: function(scope,element,attr) {
            element.find('.close').bind('click',function() {
                $('#azimutCmsSelectWidget').hide();
            })
        }
    }
});
