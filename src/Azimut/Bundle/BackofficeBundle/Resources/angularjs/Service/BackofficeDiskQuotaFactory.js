/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-04 15:13:03
 */

'use strict';
angular.module('azimutBackoffice.service')

.factory('BackofficeDiskQuotaFactory', [
'$log', '$http',
function($log, $http) {

    $log = $log.getInstance('BackofficeDiskQuotaFactory');

    var urlPrefix = 'azimut_backoffice_api_';

    var quotaInfos = {};

    var updateInfos = function() {
        $http.get(Routing.generate(urlPrefix+'get_diskquota')).then(function(response){
            quotaInfos.diskQuota = response.data.diskQuota;
            quotaInfos.diskUsage = response.data.diskUsage;
            quotaInfos.diskUsagePercent = response.data.diskUsagePercent;
            quotaInfos.diskUnit = response.data.diskUnit;

            $log.info("Updated disk quota infos", quotaInfos);
        });
    }

    return {
        getInfos: function() {
            updateInfos();
            return quotaInfos;
        },
        updateInfos: updateInfos,
        infos: function() {
            return quotaInfos
        }
    }

}]);
