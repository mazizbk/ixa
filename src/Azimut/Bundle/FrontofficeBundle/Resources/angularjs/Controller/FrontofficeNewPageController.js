/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:36:32
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeNewPageController', [
'$log', '$scope', '$rootScope','FormsBag', 'FrontofficeSiteFactory', '$state', '$stateParams', 'NotificationService', '$timeout', 'FrontofficeMenu', 'FrontofficePage', 'StringExtra', '$templateCache',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteFactory, $state, $stateParams, NotificationService, $timeout, FrontofficeMenu, FrontofficePage, StringExtra, $templateCache) {
    $log = $log.getInstance('FrontofficeNewPageController');

    $scope.$parent.showContentView = true;

    $scope.forms = new FormsBag();

    $scope.pageTypes = FrontofficeSiteFactory.pageTypes();

    $scope.formPageTemplateLoaded = false;

    var unbindPageLinkTypeWatcher = null;

    var loadPageFormType = function(type) {
        $scope.formPageTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_page_form', {type: type});
        $templateCache.remove($scope.formPageTemplateUrl);
        $scope.formPageTemplateLoaded = false;

        var unbindFormTemplateLoadedWatcher = $scope.$watch('formPageTemplateLoaded', function(newValue) {
            if (true == newValue) {

                if (angular.isFunction(unbindPageLinkTypeWatcher)) unbindPageLinkTypeWatcher();

                // reset form data
                $scope.forms.data.page = {
                    parentPage: $stateParams.pageId,
                    menu: $stateParams.menuId,
                    type: type,
                    active: true,
                    autoSlug: true,
                    showInMenu: true,
                    pageType: {}
                };

                if ('placeholder' != type && 'link' != type) {
                    $scope.forms.data.page.autoMetas = true;
                }

                // "link" page defaults
                if ('link' == type) {
                    $scope.forms.data.page.pageType.linkType = 'internal';

                    unbindPageLinkTypeWatcher = $scope.$watch('forms.data.page.pageType.linkType', function(linkType) {
                        if ('external' == linkType) {
                            $scope.forms.data.page.pageType.targetPage = null;
                        }
                        else {
                            $scope.forms.data.page.pageType.url = null;
                        }
                    });
                }

                unbindFormTemplateLoadedWatcher();
            }
        });


    };

    // display page content form by default
    $scope.currentPageTypeName = 'content';
    loadPageFormType($scope.currentPageTypeName);

    $scope.$watch('currentPageTypeName', function(newValue) {
        if(undefined != newValue) loadPageFormType(newValue);
    });

    if (null != $stateParams.pageId) {
        var parentPage = FrontofficeSiteFactory.findPage($stateParams.pageId);
        $scope.parentPage = parentPage;
    }
    else {
        var menu = FrontofficeSiteFactory.findMenu($stateParams.menuId);
        $scope.menu = menu;
    }

    $scope.formLocale = $rootScope.locale;

    $scope.addPage = function(pageData) {
        return FrontofficeSiteFactory.createPage(pageData).then(function(response) {
            var page = response.data.page;

            // remove dirty state on form
            if (undefined != $scope.forms.params.page.formController) {
                $scope.forms.params.page.formController.$setPristine();
            }

            // expand parent children in sites tree
            page.parentElement.showChildren = true;

            if (page.parentElement instanceof FrontofficeMenu) {
                $state.go('backoffice.frontoffice.menu_detail', {id: page.parentElement.id});
            }
            else {
                if ('content' == page.pageType) {
                    $state.go('backoffice.frontoffice.page_detail.parameters', {pageId: page.parentElement.id});
                }
                else {
                    $state.go('backoffice.frontoffice.page_detail.zones', {pageId: page.parentElement.id});
                }

            }

            NotificationService.addSuccess(Translator.trans('notification.success.page.create'));

            // clear form error messages
            delete $scope.forms.errors.page;
        }, function(response) {
            $log.error('Create page failed', response);
            NotificationService.addError(Translator.trans('notification.error.page.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.page = response.data.errors;
            }
        });
    }

    $scope.forms.params.page = {
        submitActive: true,
        submitLabel: Translator.trans('create.page'),
        cancelLabel: Translator.trans('cancel'),
        submitAction: function() {
            return $scope.addPage($scope.forms.data.page);
        },
        cancelAction: function() {
            if(null != $stateParams.pageId) {
                $state.go('backoffice.frontoffice.page_detail', {pageId: $stateParams.pageId});
            }
            else {
                $state.go('backoffice.frontoffice.menu_detail', {id: $stateParams.menuId});
            }
        },
        confirmDirtyDataStateChangeMessage: Translator.trans('page.has.not.been.saved.are.you.sure.you.want.to.continue')
    };

    $scope.slugAutoValue = [];

    $scope.$watchCollection('forms.data.page.menuTitle', function(menuTitle) {
        if(undefined == menuTitle) return;

        for (var i = $scope.locales.length - 1; i >= 0; i--) {
            if (undefined != menuTitle[$scope.locales[i]]) {
                $scope.slugAutoValue[$scope.locales[i]] = StringExtra.slugify(menuTitle[$scope.locales[i]]);
            }
        };
    });

}]);
