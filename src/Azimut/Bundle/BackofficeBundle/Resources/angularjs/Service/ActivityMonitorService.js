/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-17 16:06:36
 *
 ******************************************************************************
 *
 * This service gives information about visibility and activity on the current
 * browser document
 *
 * Based on mouse movements, it set a isUserActive property
 *
 * Visibily part of service is based on browser visibility API
 * It broadcasts an event on rootScope (visibilityChange)
 * and set a property on itself (ActivityMonitorService.isDocumentHidden)
 *
 * Usage :
 *     $scope.$on('documentVisibilityChange', function(event, isHidden) {
 *         if (isHidden) {
 *             // The browser page is not visible
 *         }
 *         else {
 *            // The browser page is visible
 *         }
 *     });
 *
 *     or
 *
 *     if(VisibilityService.isDocumentHidden) {
 *         // The browser page is not visible
 *     }
 *     else {
 *        // The browser page is visible
 *     }
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.service('ActivityMonitorService', [
'$log', '$rootScope', '$timeout',
function($log, $rootScope, $timeout) {

    $log = $log.getInstance('ActivityMonitorService');

    var service = this;
    var timeoutPromise = null;

    service.inactivityDectectionDelay = 10; // in minutes
    service.isDocumentHidden = false;
    service.isUserActive = true;
    service.lastUserActionTime = new Date();

    function visibilityChange() {
        service.isDocumentHidden = document.hidden || document.webkitHidden || document.mozHidden || document.msHidden;
        if(undefined == service.isDocumentHidden) service.isDocumentHidden = false;
        $rootScope.$broadcast('documentVisibilityChange', service.isDocumentHidden);
    }
    document.addEventListener("visibilitychange", visibilityChange);
    document.addEventListener("webkitvisibilitychange", visibilityChange);
    document.addEventListener("msvisibilitychange", visibilityChange);

    function detectUserActivity() {
        service.lastUserActionTime = new Date();
        service.isUserActive = true;

        $timeout.cancel(timeoutPromise);

        timeoutPromise = $timeout(function() {
            service.isUserActive = false;
            $log.info('Detected user inactivity since '+service.inactivityDectectionDelay+' min');
        }, service.inactivityDectectionDelay*60*1000);
    }
    document.addEventListener("touchstart", detectUserActivity);
    document.addEventListener("mousemove", detectUserActivity);

}]);
