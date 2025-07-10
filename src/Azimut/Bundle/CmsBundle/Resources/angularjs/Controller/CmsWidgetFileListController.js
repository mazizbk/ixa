/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-25 11:53:26
 */

'use strict';

angular.module('azimutCms.controller')

.controller('CmsWidgetFileListController', [
'$log', '$scope', 'CmsFileFactory', '$state', '$stateParams',
function($log, $scope, CmsFileFactory, $state, $stateParams) {
    $log = $log.getInstance('CmsWidgetFileListController');

    var statePrefix = $state.$current.parent.self.name;

    $scope.files = CmsFileFactory.files();

    $scope.type = $stateParams.cmsFileType;

    $scope.selectedFileId = null;
    $scope.selectedFileName = null;

    $scope.cmsParams = {
        acceptedTypes: []
    };

    //list of possible file types
    $scope.availableFileTypes = CmsFileFactory.availableFileTypes();
    $scope.allowedFileTypes = $scope.availableFileTypes;

    $scope.$watch('appScope.widgetId', function(newValue, oldValue) {
        if (undefined != newValue) {
            $log.info("Cms widget loaded for ",$scope.appScope.widgetId);
            $scope.cmsParams = $scope.appScope.azimutWidgetsParams[$scope.appScope.widgetId].params;
        }
    });

    $scope.$watch('cmsParams', function(newValue, oldValue) {
        if (undefined != newValue && undefined != newValue.acceptedTypes && newValue.acceptedTypes.length > 0) {
            $scope.allowedFileTypes = filterAvailablesFileTypes($scope.availableFileTypes, newValue.acceptedTypes);
        }
    });

    if(null != $scope.appScope.widgetId) {
        $log.info("Cms widget loaded for ",$scope.appScope.widgetId);
        $scope.cmsParams = $scope.appScope.azimutWidgetsParams[$scope.appScope.widgetId].params;
    }

    // remove from availableFileTypes types not present in acceptedTypes
    function filterAvailablesFileTypes(availableFileTypes, acceptedTypes) {
        var filteredFiles = [];

        for (var i=0; i<availableFileTypes.length; i++) {
            for(var j=0; j<acceptedTypes.length; j++) {
                if(availableFileTypes[i].name == acceptedTypes[j]) {
                    filteredFiles.push(availableFileTypes[i]);
                }
            }
        }

        return filteredFiles;
    }

    $scope.selectFile = function(file) {
        $scope.selectedFileId = file.id;
        $scope.selectedFileName = file.getName($scope.locale);
    };

    $scope.widgetCallback = function(cmsFiles) {
        var options = null;

        $scope.azimutWidgetsParams[$scope.widgetId].callbacks['azimutCmsChooseCmsFiles'](cmsFiles,options);
    };

    $scope.widgetSelectCmsFile = function(cmsFileId) {
        var cmsFile = CmsFileFactory.findFile(cmsFileId);

        if (undefined == cmsFile) {
            $log.error('Trying to select a undefined file in cms widget (id: '+ cmsFileId +')');
            return false;
        }

        $scope.selectedFileName = cmsFile.getName($scope.locale);

        $scope.widgetCallback([{
            id: cmsFile.id,
            name: cmsFile.getName($scope.locale)
        }]);
    };

    $scope.widgetNewFile = function(cmsFileType) {
        $state.go(statePrefix + '.widget_select_new_file', {cmsFileType: cmsFileType});
    };

    if (null != $stateParams.preselectId && '' != $stateParams.preselectId) {
        var file = CmsFileFactory.findFile($stateParams.preselectId);
        if (file) {
            $scope.selectFile(file);
        }
    }
}]);
