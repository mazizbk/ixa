/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-11-03 12:40:57
 */

'use strict';

angular.module('azimutCmsContact.controller')
.controller('CmsContactNewContactController', [
'$scope', '$controller', '$state', '$log',
function ($scope, $controller, $state, $log) {
    // extend CmsFileNewController
    angular.extend(this, $controller('CmsNewFileController', {$scope: $scope}));

    $log = $log.getInstance('CmsContactNewContactController');

    $scope.stateGoBack = function(contact) {
        $state.go('backoffice.cmscontact.contact_detail', {file_id: contact.id});
    };
}]);
