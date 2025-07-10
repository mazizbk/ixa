/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:13:36
 */

'use strict';

angular.module('azimutCmsMap.controller', []);
angular.module('azimutCmsMap.directive', []);
angular.module('azimutCmsMap.service', []);
angular.module('azimutCmsMap.filter', []);

angular.module('azimutCmsMap', [
    'azimutBackoffice',
    'azimutCms',
    'azimutCmsMap.controller',
    'azimutCmsMap.directive',
    'azimutCmsMap.service',
    'azimutCmsMap.filter',
    'ui.router',
    'ui.event',
    'azimutCms'
])

.config([
'$stateProvider', 'CmsStateProvider',
function($stateProvider, CmsStateProvider) {
    $stateProvider
        //main state
        .state('backoffice.cmsmap', {
            url: "/map",
            templateUrl: Routing.generate('azimut_cmsmap_backoffice_jsview_main'),
            resolve: {
                fileFactoryInitPromise: function(CmsFileFactory) {
                    return CmsFileFactory.init('CmsMap');
                }
            },
            controller: 'CmsMapMainController'
        })

        .state('backoffice.cmsmap.map_point_detail', {
            url: '/map/:file_id',
            params: {
                cmsFileType: 'map_point',
            },
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_detail'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.cmsmap.map_point_detail';
                }
            },
            controller: 'CmsMapMapPointDetailController'
        })

        .state('backoffice.cmsmap.new_map_point', {
            url: '/new_map',
            params: {
                cmsFileType: 'map_point',
            },
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_new_file'),
            controller: 'CmsMapNewMapPointController'
        })
    ;

    CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.cmsmap.map_point_detail');
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {
    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('cms_map.app.name'),
        icon: 'glyphicon glyphicon-map-marker',
        stateName: 'backoffice.cmsmap',
        displayOrder: 5
    });
}])

;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutCmsMap');
