/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:34:46
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficePageDetailController', [
'$log', '$scope', '$rootScope','FormsBag', 'FrontofficeSiteFactory', 'ArrayExtra', '$state', '$stateParams', 'NotificationService', '$timeout', '$window', 'azConfirmModal', 'StringExtra', 'FrontofficePage', '$templateCache',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteFactory, ArrayExtra, $state, $stateParams, NotificationService, $timeout, $window, azConfirmModal, StringExtra, FrontofficePage, $templateCache) {
    $log = $log.getInstance('FrontofficePageDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    var page = FrontofficeSiteFactory.findPage($stateParams.pageId);

    if (!page) {
        NotificationService.addCriticalError(Translastor.trans('notification.error.page.with.id.%id%.get', { 'id' : $stateParams.id }));
        $scope.$parent.showContentView = false;
        return;
    }

    $scope.formPageTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_page_form', {type: page.pageType});
    $templateCache.remove($scope.formPageTemplateUrl);

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    $scope.pageEditIsGranted = null;

    var currentPageLayoutId = null;

    (function loadPageData() {

        $scope.reloadPageData = loadPageData;

        //Fetch the complete version of the page, with all fields
        FrontofficeSiteFactory.getPage(page.id, 'all').then(function(response) {
            var page = response.data.page;

            var pageType = page.pageType;

            if ('content' == pageType) {
                var layoutPreviewName = page.layout.template.substring(0, page.layout.template.indexOf('.'));
                $scope.layoutPreviewUrl = Routing.generate('azimut_frontoffice_backoffice_layout_preview', {template: layoutPreviewName});
                $scope.layoutDefaultPreviewUrl = Routing.generate('azimut_frontoffice_backoffice_layout_preview', {template: 'default'});
                currentPageLayoutId = page.layout.id;
            }

            // we don't use the real object because we need raw data to be binded into the form
            $scope.forms.data.page = page.toFormData();
            $scope.page = page;
            $scope.pageEditIsGranted = response.data.pageEditIsGranted;


            var submitFunction = function() {
                return $scope.savePage($scope.forms.data.page);
            };

            $scope.forms.params.page = {
                submitActive: true,
                submitLabel: Translator.trans('update'),
                cancelLabel: Translator.trans('cancel'),
                submitAction: function() {
                    // ask confirmation if layout changed
                    if ('content' == pageType && currentPageLayoutId != $scope.forms.data.page.pageType.layout) {
                        azConfirmModal(Translator.trans('page.layout.confirm.change')).result.then(submitFunction);
                    }
                    else {
                        return submitFunction();
                    }
                },
                cancelAction: function() {
                    $scope.reloadPageData();
                    if ('content' == $scope.page.pageType) {
                        $scope.openPage($scope.page);
                    }
                    else {
                        if ($scope.page.parentElement instanceof FrontofficePage) {
                            $scope.openPage($scope.page.parentElement);
                        }
                        else {
                            $scope.openMenu($scope.page.parentElement);
                        }
                    }
                },
                confirmDirtyDataStateChangeMessage: Translator.trans('page.has.not.been.saved.are.you.sure.you.want.to.continue')
            };

            $scope.mainContentLoaded();
        });

    }());




    $scope.breadcrumb = {
        elements: []
    };

    var breadcrumbCurrentFile = page;

    do {
        $scope.breadcrumb.elements.unshift(breadcrumbCurrentFile);
    } while(breadcrumbCurrentFile = breadcrumbCurrentFile.parentElement);


    $scope.getZoneByName = function (zoneName) {
        return ArrayExtra.findFirstInArray($scope.page.zones, {'name':zoneName});
    }

    $scope.savePage = function(pageData) {
        return FrontofficeSiteFactory.updatePage(pageData).then(function(response) {
            var page = response.data.page;

            // remove dirty state on form
            if (undefined != $scope.forms.params.page.formController) {
                $scope.forms.params.page.formController.$setPristine();
            }

            $scope.page = page;
            $scope.forms.data.page = page.toFormData();

            if ('content' == page.pageType) {
                currentPageLayoutId = page.layout.id;
            }

            if ('content' == page.pageType) {
                var layoutPreviewName = page.layout.template.substring(0,page.layout.template.indexOf('.'));
                $scope.layoutPreviewUrl = Routing.generate('azimut_frontoffice_backoffice_layout_preview',{template: layoutPreviewName});

                $state.go('backoffice.frontoffice.page_detail.zones', {pageId: page.id});
                $scope.openPage(page);
            }

            NotificationService.addSuccess(Translator.trans('notification.success.page.update'));

            // clear form error messages
            delete $scope.forms.errors.page;
        }, function(response) {
            $log.error('Update page failed', response);
            NotificationService.addError(Translator.trans('notification.error.page.update'), response);

            if (undefined != response.data.errors) {
                $scope.forms.errors.page = response.data.errors;
            }
        });
    }

    $scope.openPageParameters = function(page) {
        $state.go('backoffice.frontoffice.page_detail.parameters', {pageId: page.id});
    };

    $scope.openPageContent = function(page) {
        if ('content' != page.pageType) {
            $scope.openPageParameters(page);
        }

        // if page has only one zone with only one cmsFile
        if (true == page.isFullPageCmsFile) {
            $state.go('backoffice.frontoffice.page_detail.freecontent', {pageId: page.id, file_id: page.fullPageCmsFileId});
            return;
        }

        // if page has only one zone, open specific view
        if (true == page.isMonoZone) {
            $state.go('backoffice.frontoffice.page_detail.monozone', {pageId: page.id, zoneId: page.monoZoneId});
            return;
        }

        $state.go('backoffice.frontoffice.page_detail.zones', {pageId: page.id});

    };

    $scope.openZone = function(zone) {

        // if zone has only one attachment max that cannot be deleted, switch to shortcut view
        if (true == zone.isFullZoneCmsFile) {
            $state.go('backoffice.frontoffice.zone_detail.freecontent', {zoneId: zone.id, file_id: zone.fullZoneCmsFileId});
            return;
        }

        $state.go('backoffice.frontoffice.zone_detail.content', {zoneId: zone.id});
    }

    $scope.openPagePreview = function(page) {
        var fullSlug = page.fullSlug;

        if (!angular.isString(fullSlug)) {
            if (undefined == fullSlug[$rootScope.locale]) {
                for (locale in fullSlug) {
                    fullSlug = fullSlug[locale];
                }
            }
            else {
                fullSlug = fullSlug[$rootScope.locale];
            }
        }

        $window.open(page.siteUri + Routing.generate('azimut_frontoffice', {'path': fullSlug}), 'azimut.pagepreview','menubar=no,status=no,scrollbars=yes');
    }

    $scope.slugAutoValue = [];

    $scope.$watchCollection('forms.data.page.menuTitle', function(menuTitle) {
        if(undefined == menuTitle) return;

        for (var i = $scope.locales.length - 1; i >= 0; i--) {
            if (undefined != menuTitle[$scope.locales[i]]) {
                $scope.slugAutoValue[$scope.locales[i]] = StringExtra.slugify(menuTitle[$scope.locales[i]]);
            }
        };
    });

    $scope.state = $state;
}]);
