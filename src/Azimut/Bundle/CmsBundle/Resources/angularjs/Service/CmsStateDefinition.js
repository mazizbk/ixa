/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-25 11:59:32
 */

'use strict';
angular.module('azimutCms.service')

.constant('CmsStateDefinition', {
    widgetSelectFile: {
        name: 'widget_select_file',
        url: '/files_:type-:preselectId',
        views: {
            'cms-select-widget': {
                templateUrl: Routing.generate('azimut_cms_backoffice_jsview_widget_select_file_list'),
                resolve: {
                    fileFactoryInitPromise: function(CmsFileFactory){
                        return CmsFileFactory.init();
                    }
                },
                controller: 'CmsWidgetFileListController'
            }
        }
    },
    widgetSelectNewFile: {
        name: 'widget_select_new_file',
        url: '/new_file_:cmsFileType',
        views: {
            'cms-select-widget': {
                templateUrl: Routing.generate('azimut_cms_backoffice_jsview_widget_select_new_file'),
                controller: 'CmsWidgetSelectNewFileController'
            }
        }
    },
    widgetFileEdit: {
        name: 'widget_edit_file',
        url: '/file_:file_id',
        views: {
            'cms-edit-widget': {
                templateUrl: Routing.generate('azimut_cms_backoffice_jsview_widget_edit_file_detail'),
                controller: 'CmsWidgetDetailController'
            }
        }
    }
});
