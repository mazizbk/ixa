/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-25 13:39:17
 */

'use strict';

angular.module('azimutBackoffice.controller')

.controller('BackofficeDebugController', [
'$scope', '$log', '$http',
function($scope, $log, $http) {

    $log = $log.getInstance('BackofficeDebugController');

    $scope.$log = $log;
    $scope.email = '';
    $scope.message = '';
    $scope.confirmMessageSent = null;
    $scope.errorMessageSent = null;
    $scope.showLog = false;

    $scope.setPageTitle('Report bug');

    $scope.sendEmailDebug = function() {
        $scope.confirmMessageSent = null;
        $scope.errorMessageSent = null;

        var trace = $log.history.join("\n");

        $http.post(Routing.generate('azimut_backoffice_api_post_emailbugreport'), {email: $scope.email, message: $scope.message, trace: trace}).then(function(response){
            $log.info("debug email sent");
            $scope.confirmMessageSent = 'Message has been sent. Your bug number: '+ response.data.bugNumber;
            $scope.email = '';
            $scope.message = '';
        }, function(response) {
            $log.error("could not send email message");
            $scope.errorMessageSent = 'Message could not be sent';
        });
    };

}]);
