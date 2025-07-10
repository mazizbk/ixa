/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 14:22:29
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeSiteLayoutFactory', [
'$log', '$http', '$rootScope', '$q',
function($log, $http, $rootScope, $q) {
    $log = $log.getInstance('FrontofficeSiteLayoutFactory');

    var factory = this;
    factory.urlPrefix = 'azimut_frontoffice_api_';

    return {

        getSiteLayouts: function(locale) {
            if(null == locale) locale = $rootScope.locale;
            return $http.get(Routing.generate(factory.urlPrefix+'get_sitelayouts')+'?locale='+locale).then(function(response) {
                return response.data.siteLayouts;
            });
        },

        createSiteLayout: function(siteLayoutData) {
            return $http.post(Routing.generate(factory.urlPrefix+'post_sitelayouts'), {site_layout: siteLayoutData});
        },

        getSiteLayout: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;
            return $http.get(Routing.generate(factory.urlPrefix+'get_sitelayout', {id: id})+'?locale='+locale).then(function(response) {
                return response.data.siteLayout;
            });;
        },

        updateSiteLayout: function(siteLayoutData) {
            var siteLayoutApiData = angular.copy(siteLayoutData);

            var siteLayoutId = siteLayoutApiData.id;
            delete siteLayoutApiData.id;

            return $http.put(Routing.generate(factory.urlPrefix+'put_sitelayout', {id: siteLayoutId}),{site_layout: siteLayoutApiData});
        },

        deleteSiteLayout: function(siteLayout) {
            return $http.delete(Routing.generate(factory.urlPrefix+'delete_sitelayout',{ id: siteLayout.id }));
        }
    }
}]);
