/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2013-09-23
 */

'use strict';

angular.module('azimutCms.controller', []);
angular.module('azimutCms.directive', []);
angular.module('azimutCms.service', []);
angular.module('azimutCms.filter', []);

angular.module('azimutCms', [
    'azimutBackoffice',
    'azimutMediacenter',
    'azimutCms.controller',
    'azimutCms.directive',
    'azimutCms.service',
    'azimutCms.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider', 'MediacenterStateProvider', 'CmsStateProvider',
function($stateProvider, MediacenterStateProvider, CmsStateProvider) {

    $stateProvider

        .state('backoffice.cms', {
            url: "/cms",
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_main'),
            resolve: {
                fileFactoryInitPromise: function(CmsFileFactory) {
                    return CmsFileFactory.init('Cms');
                }
            },
            controller: 'CmsMainController'
        })

        .state('backoffice.cms.trash_bin', {
            url: '/trash_bin',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_trash_bin'),
            controller: 'CmsTrashBinController'
        })

        .state('backoffice.cms.file_list', {
            url: '/files_:cmsFileType',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_list'),
            controller: 'CmsFileListController'
        })

        .state('backoffice.cms.new_file', {
            url: '/new_file_:cmsFileType',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_new_file'),
            controller: 'CmsNewFileController'
        })

        .state('backoffice.cms.file_detail', {
            url: '/files_:cmsFileType/file_:file_id',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_detail'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.cms.file_detail';
                }
            },
            controller: 'CmsFileDetailController'
        })
    ;

    CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.cms.file_detail');

    MediacenterStateProvider.attachStatesTo('backoffice.cms.new_file');
    MediacenterStateProvider.attachStatesTo('backoffice.cms.file_detail');

}])

// This function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('cms.app.name'),
        icon: 'glyphicon-list-alt',
        stateName: 'backoffice.cms',
        displayOrder: 3
    });

}])
;

// Inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutCms');
