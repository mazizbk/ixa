/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:06:31
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsMainController',[
'$log', '$scope', '$rootScope', 'CmsFileFactory', '$state', 'NotificationService',
function($log, $scope, $rootScope, CmsFileFactory, $state, NotificationService) {

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    $log = $log.getInstance('CmsMainController');

    if(!CmsFileFactory.isGrantedUser()) {
        $log.warn("User has not access to CmsFileFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'cms'});
        return;
    }

    $scope.Translator = Translator;
    $scope.Routing = Routing;

    $scope.setPageTitle(Translator.trans('cms.meta.title'));

    //available locales in application
    if(null == $rootScope.locales) $rootScope.locales = ['en'];

    //current locale in interface
    if(null == $rootScope.locale) $rootScope.locale = 'en';

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    $scope.isMainContentLoading = false;

    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    };

    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    };

    //when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    //list of possible file types
    $scope.availableFileTypes = angular.copy(CmsFileFactory.availableFileTypes());

    for (var i = $scope.availableFileTypes.length - 1; i >= 0; i--) {
        $scope.availableFileTypes[i].translatedName = Translator.transChoice('cms.file.type.' + $scope.availableFileTypes[i].name, 1);
    }

    $scope.openFileList = function(type) {
        $state.go('backoffice.cms.file_list',{cmsFileType: type});
    };

    $scope.trashBin = {
        cmsFileType: 'trash_bin'
    };

    // handler for cmsFile dropped on trash bin
    $scope.$on('dropEvent', function(evt, dragged, dropped, droppedFiles) {

        //we have dropped an element dragged into the interface
        if(!dragged) return;

        // if dropped is not a file, then ignore it
        if(undefined == dropped.cmsFileType) return;

        // this handler is for dropping on trash bin only
        if(dropped.cmsFileType != 'trash_bin') {
            return;
        }

        if(undefined != dragged.cmsFileType) {
            $scope.trashFile(dragged);
        }
        else {
            $log.error("Could not handle dropped element on trash bin");
        }

    });

    $scope.openTrashBin = function() {
        $state.go('backoffice.cms.trash_bin');
    };

    $scope.trashFile = function(file) {
        CmsFileFactory.trashFile(file).then(function (response) {
            $log.info("File has been trashed", file);
            NotificationService.addSuccess(Translator.trans('notification.success.cms_file.trash'), response);
        }, function(response) {
            $log.error('Error while trashing cmsFile ', response);
            NotificationService.addError(Translator.trans('notification.error.cms_file.trash'), response);
        });

    };

    //TODO: by setting this to false, the mediacenter widget should be instantiate at first call of loadMediacenterWidget
    //BUT for an unknown reason, binding of mediacenter_widget_active does not work in function
    /*$scope.mediacenter_widget_active = true;
    $scope.mediacenter_widget_url = Routing.generate('azimut_mediacenter_backoffice_widget');

    $scope.loadMediacenterWidget = function() {
        $log.debug("loading mediacenter widget");

        $log.debug("loadMediacenterWidget scope : ",$scope);
        $scope.mediacenter_widget_active = true;
    }*/

    //at startup, go to "all types" state
    if($state.current.name == 'backoffice.cms') $state.go('backoffice.cms.file_list');
}])

;
