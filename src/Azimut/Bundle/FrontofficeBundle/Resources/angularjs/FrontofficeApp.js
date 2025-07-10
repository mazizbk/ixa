/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2014-01-10 16:39:05
 */

'use strict';

angular.module('azimutFrontoffice.controller', []);
angular.module('azimutFrontoffice.directive', []);
angular.module('azimutFrontoffice.service', []);
angular.module('azimutFrontoffice.filter', []);

angular.module('azimutFrontoffice', [
    'azimutBackoffice',
    'azimutCms',
    'azimutFrontoffice.controller',
    'azimutFrontoffice.directive',
    'azimutFrontoffice.service',
    'azimutFrontoffice.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider', 'CmsStateProvider', 'MediacenterStateProvider',
function($stateProvider, CmsStateProvider, MediacenterStateProvider) {

    $stateProvider

        .state('backoffice.frontoffice', {
            url: "/frontoffice",
            templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_main'),
            resolve: {
                siteFactoryInitPromise: function(FrontofficeSiteFactory) {
                    return FrontofficeSiteFactory.init();
                }
            },
            controller: 'FrontofficeMainController'
        })

            .state('backoffice.frontoffice.list', {
                url: '/sites',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_sites_list'),
                controller: 'FrontofficeSitesListController'
            })

            .state('backoffice.frontoffice.new_site', {
                url: '/new-site',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_new_site'),
                controller: 'FrontofficeNewSiteController'
            })

            .state('backoffice.frontoffice.new_page_in_menu', {
                url: '/menu/:menuId/new-page',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_new_page'),
                controller: 'FrontofficeNewPageController'
            })

            .state('backoffice.frontoffice.new_page_in_page', {
                url: '/page/:pageId/new-page',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_new_page'),
                controller: 'FrontofficeNewPageController'
            })

            .state('backoffice.frontoffice.site_detail', {
                url: '/site/:id',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_site_detail'),
                controller: 'FrontofficeSiteDetailController'
            })

            .state('backoffice.frontoffice.menu_detail', {
                url: '/menu/:id',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_menu_detail'),
                controller: 'FrontofficeMenuDetailController'
            })

            .state('backoffice.frontoffice.page_detail', {
                url: '/page/:pageId',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_page_detail'),
                controller: 'FrontofficePageDetailController'
            })

                .state('backoffice.frontoffice.page_detail.zones', {
                    url: '/zones',
                    templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_page_detail_zones'),
                    controller: 'FrontofficePageDetailZonesController'
                })

                .state('backoffice.frontoffice.page_detail.parameters', {
                    url: '/parameters',
                    templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_page_detail_parameters')
                })

                .state('backoffice.frontoffice.page_detail.monozone', {
                    url: '/monozone/:zoneId',
                    templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_zone_detail_content'),
                    controller: 'FrontofficeZoneDetailController'
                })

                .state('backoffice.frontoffice.page_detail.freecontent', {
                    url: '/freecontent/:file_id',
                    params: {
                        cmsFileType: 'page',
                    },
                    templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_detail_embedded'),
                    resolve: {
                        baseStateName: function() {
                            return 'backoffice.frontoffice.page_detail.freecontent';
                        }
                    },
                    controller: 'FrontofficeFreecontentDetailController'
                })

            .state('backoffice.frontoffice.zone_detail', {
                url: '/zone/:zoneId',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_zone_detail'),
                controller: 'FrontofficeZoneDetailController'
            })

                .state('backoffice.frontoffice.zone_detail.content', {
                    url: '/contents',
                    templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_zone_detail_content'),
                })

                .state('backoffice.frontoffice.zone_detail.freecontent', {
                    url: '/freecontent/:file_id',
                    params: {
                        cmsFileType: 'none',
                    },
                    templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_detail_embedded'),
                    resolve: {
                        baseStateName: function() {
                            return 'backoffice.frontoffice.zone_detail.freecontent';
                        }
                    },
                    controller: 'FrontofficeZoneFreecontentDetailController'
                })

            .state('backoffice.frontoffice.site_layouts_list', {
                url: '/sitelayout',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_site_layouts_list'),
                controller: 'FrontofficeSiteLayoutsListController'
            })

            .state('backoffice.frontoffice.site_layout_detail', {
                url: '/sitelayout/:id',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_site_layout_detail'),
                controller: 'FrontofficeSiteLayoutDetailController'
            })

            .state('backoffice.frontoffice.new_site_layout', {
                url: '/new-site-layout',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_new_site_layout'),
                controller: 'FrontofficeNewSiteLayoutController'
            })

            .state('backoffice.frontoffice.page_layouts_list', {
                url: '/pagelayout',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_page_layouts_list'),
                controller: 'FrontofficePageLayoutsListController'
            })

            .state('backoffice.frontoffice.page_layout_detail', {
                url: '/pagelayout/:id',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_page_layout_detail'),
                controller: 'FrontofficePageLayoutDetailController'
            })

            .state('backoffice.frontoffice.new_page_layout', {
                url: '/new-page-layout',
                templateUrl: Routing.generate('azimut_frontoffice_backoffice_jsview_new_page_layout'),
                controller: 'FrontofficeNewPageLayoutController'
            })
    ;

    // attach cms widget states for cmsfile adding
    CmsStateProvider.attachWidgetSelectFileStatesTo('backoffice.frontoffice.zone_detail.content');
    CmsStateProvider.attachWidgetSelectFileStatesTo('backoffice.frontoffice.page_detail.monozone');

    // attach cms widget states for cmsfile direct edit
    CmsStateProvider.attachWidgetFileEditStatesTo('backoffice.frontoffice.zone_detail.content');
    CmsStateProvider.attachWidgetFileEditStatesTo('backoffice.frontoffice.page_detail.monozone');

    CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.frontoffice.page_detail.freecontent');
    MediacenterStateProvider.attachStatesTo('backoffice.frontoffice.page_detail.freecontent');
    CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.frontoffice.zone_detail.freecontent');
    MediacenterStateProvider.attachStatesTo('backoffice.frontoffice.zone_detail.freecontent');
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('frontoffice.app.name'),
        icon: 'glyphicon-globe',
        stateName: 'backoffice.frontoffice',
        displayOrder: 4
    });

}])

;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutFrontoffice');
