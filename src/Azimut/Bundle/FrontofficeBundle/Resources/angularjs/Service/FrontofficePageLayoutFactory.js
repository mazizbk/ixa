/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 10:52:51
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficePageLayoutFactory', [
'$log', '$http', '$rootScope', '$q',
function($log, $http, $rootScope, $q) {
    $log = $log.getInstance('FrontofficePageLayoutFactory');

    var factory = this;
    factory.urlPrefix = 'azimut_frontoffice_api_';

    return {

        getPageLayouts: function(locale) {
            if(null == locale) locale = $rootScope.locale;
            return $http.get(Routing.generate(factory.urlPrefix+'get_pagelayouts')+'?locale='+locale).then(function(response) {
                return response.data.pageLayouts;
            });
        },

        createPageLayout: function(pageLayoutData) {
            return $http.post(Routing.generate(factory.urlPrefix+'post_pagelayouts'), {page_layout: pageLayoutData});
        },

        getPageLayout: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;
            return $http.get(Routing.generate(factory.urlPrefix+'get_pagelayout', {id: id})+'?locale='+locale).then(function(response) {
                for (var i = response.data.pageLayout.zoneDefinitions.length - 1; i >= 0; i--) {
                    if (null != response.data.pageLayout.zoneDefinitions[i].targetZoneId) {
                        response.data.pageLayout.zoneDefinitions[i].targetZone = response.data.pageLayout.zoneDefinitions[i].targetZoneId;
                        delete response.data.pageLayout.zoneDefinitions[i].targetZoneId;
                    }
                }

                return response.data.pageLayout;
            });;
        },

        updatePageLayout: function(pageLayoutData) {
            var pageLayoutApiData = angular.copy(pageLayoutData);

            var pageLayoutId = pageLayoutApiData.id;
            delete pageLayoutApiData.id;

            return $http.put(Routing.generate(factory.urlPrefix+'put_pagelayout', {id: pageLayoutId}),{page_layout: pageLayoutApiData});
        },

        deletePageLayout: function(pageLayout) {
            return $http.delete(Routing.generate(factory.urlPrefix+'delete_pagelayout',{ id: pageLayout.id }));
        }
    }

}]);
