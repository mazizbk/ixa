/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:34:13
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeMenuDetailController', [
'$log', '$scope','FormsBag', 'FrontofficeSiteFactory', 'ArrayExtra', '$state', '$stateParams', 'NotificationService', '$timeout',
function($log, $scope, FormsBag, FrontofficeSiteFactory, ArrayExtra, $state, $stateParams, NotificationService, $timeout) {
    $log = $log.getInstance('FrontofficeMenuDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    var menu = FrontofficeSiteFactory.findMenu($stateParams.id);

    if(!menu) {
        NotificationService.addCriticalError(Translator.trans('notification.error.menu.with.id.%id%.get', { 'id' : $stateParams.id }));
        $scope.$parent.showContentView = false;
        return;
    }

    $scope.menu = menu;

    $scope.formMenuTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_menu_form');

    $scope.forms = new FormsBag();

    //Fetch the complete version of the menu, with all fields
    FrontofficeSiteFactory.getMenu(menu.id).then(function(response) {
        var menu = response.data.menu;

        // we don't use the real MediacenterFile object because we need raw data to be binded into the form
        var menuFormData = menu.toFormData();

        $scope.forms.data.menu = menuFormData;

        $scope.forms.params.menu = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.saveMenu($scope.forms.data.menu);
            },
            cancelAction: function() {
                $state.reload();
            }
        }

        $scope.mainContentLoaded();
    });

    $scope.breadcrumb = {
        elements: [
            menu.parentElement,
            menu
        ]
    };

    $scope.saveMenu = function(menuData) {
        return FrontofficeSiteFactory.updateMenu(menuData).then(function(response) {
            NotificationService.addSuccess(Translator.trans('notification.success.menu.update'));

            // clear form error messages
            delete $scope.forms.errors.menu;
        }, function(response) {
            $log.error('Update menu failed' + response);
            NotificationService.addError(Translator.trans('notification.error.menu.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.menu = response.data.errors;
            }
        });
    }
}]);
