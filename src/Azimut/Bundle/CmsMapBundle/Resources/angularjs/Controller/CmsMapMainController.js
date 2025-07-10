/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:13:36
 */

'use strict';

angular.module('azimutCmsMap.controller')

.controller('CmsMapMainController',[
'$log', '$scope','$state', 'NotificationService', 'CmsFileFactory',
function($log, $scope, $state, NotificationService, CmsFileFactory) {
    $log = $log.getInstance('CmsMapMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if(!CmsFileFactory.isGrantedUser()) {
        $log.warn("User has not access to CmsFileFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'cms_map'});
        return;
    }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('cms_map.meta.title'));

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    // clear notification at each state change
    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    // show loader in main content panel
    // set this to true if the content is loaded from this controller
    // leave it to false if the content was preloader before calling controller
    $scope.isMainContentLoading = false;

    // set function to allow easy update of loading state in children scopes
    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    // retrieve data from your entity factory like for example:
    $scope.mapPoints = CmsFileFactory.files();

    // when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    $scope.openMapPoint = function(mapPoint) {
        $state.go('backoffice.cmsmap.map_point_detail', {file_id: mapPoint.id});
    };

    $scope.deleteMapPoint = function(mapPoint) {
        CmsFileFactory.deleteFile(mapPoint).then(function (response) {
            $log.info('Map point has been deleted', response);
            $state.go('backoffice.cmsmap');
            NotificationService.addSuccess(Translator.trans('notification.success.map.point.delete'));
        }, function(response) {
            $log.error('Error while deleting map point', response);
            NotificationService.addError(Translator.trans('notification.error.map.point.delete'), response);
        });
    };
}]);
