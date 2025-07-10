/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-01 16:42:49
 */

 'use strict';

 angular.module('azimutCms.directive')

 .directive('azCmsEditWidget', function() {
     return {
         restrict: 'E',
         transclude: true,
         templateUrl: Routing.generate('azimut_cms_backoffice_jsview_widget_edit_file'),
         link: function(scope,element,attr) {
             element.find('.close').bind('click',function() {
                 $('#azimutCmsEditWidget').hide();
             })
         }
     }
 });
