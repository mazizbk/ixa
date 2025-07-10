/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:32:33
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeMainController',[
'$log', '$scope', '$rootScope', '$state', 'NotificationService', 'FrontofficeSiteFactory', 'FrontofficeSite', 'FrontofficeMenu', 'FrontofficePage', 'ArrayExtra',
function($log, $scope, $rootScope, $state, NotificationService, FrontofficeSiteFactory, FrontofficeSite, FrontofficeMenu, FrontofficePage, ArrayExtra) {
    $log = $log.getInstance('FrontofficeMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if (undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    if (!FrontofficeSiteFactory.isGrantedUser()) {
        $log.warn("User has not access to FrontofficeSiteFactory data");
        $state.go('backoffice.forbidden_app', {appName: 'frontoffice'});
        return;
    }

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;
    $scope.Routing = Routing;

    $scope.setPageTitle(Translator.trans('frontoffice.meta.title'));

    //available locales in application
    if (null == $rootScope.locales) $rootScope.locales = ['en'];

    //current locale in interface
    if (null == $rootScope.locale) $rootScope.locale = 'en';

    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    $scope.sites = FrontofficeSiteFactory.sites();

    //currently opened site, menu or page
    $scope.currentElement = null;

    $scope.showContentView = true;

    $scope.isMainContentLoading = false;

    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    };
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    };


    $scope.openSite = function(site) {
        $state.go('backoffice.frontoffice.site_detail', {id: site.id});
    };

    $scope.openMenu = function(menu) {
        $state.go('backoffice.frontoffice.menu_detail', {id: menu.id});
    };

    $scope.openPage = function(page) {
        if ('content' == page.pageType) {

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

        }
        else {
            $state.go('backoffice.frontoffice.page_detail.parameters', {pageId: page.id});
        }
    };

    $scope.openBreadcrumbElement = function(breadcrumbElement) {
        if (breadcrumbElement instanceof FrontofficeSite) {
            $scope.openSite(breadcrumbElement)
        }
        else if (breadcrumbElement  instanceof FrontofficeMenu) {
            $scope.openMenu(breadcrumbElement)
        }
        else if (breadcrumbElement instanceof FrontofficePage) {
            $scope.openPage(breadcrumbElement)
        }
        else {
            $log.error('Could not open breadcrumb element, unsupported type', breadcrumbElement);
        }
    };




    $scope.getParentElementMaxOrder = function(page) {
        var maxOrder = null;
        if (page.parentElement instanceof FrontofficeMenu) {
            maxOrder = page.parentElement.pages.length;
        }
        else {
            maxOrder = page.parentElement.childrenPages.length;
        }
        return maxOrder;
    };

    //TODO: refactor this with delete functions for menu and site
    $scope.deletePage = function(page) {
        var oldDisplayOrder = page.displayOrder;

        // reindex orders in parent (move the page to bottom)
        var maxOrder = $scope.getParentElementMaxOrder(page);
        page.setDisplayOrder(maxOrder);

        var oldParent = page.parentElement;

        // Remove from interface
        if (page.parentElement instanceof FrontofficeMenu) {
            page.parentElement.pages.splice(page.parentElement.pages.indexOf(page),1);
        }
        else if (page.parentElement instanceof FrontofficePage) {
            page.parentElement.childrenPages.splice(page.parentElement.childrenPages.indexOf(page),1);
        }

        FrontofficeSiteFactory.deletePage(page).then(function(response) {
            // go back to parent
            if (page.parentElement instanceof FrontofficeMenu) {
                $scope.openMenu(page.parentElement);
            }
            else {
                $scope.openPage(page.parentElement);
            }

            NotificationService.addSuccess(Translator.trans('notification.success.page.delete'));

        }, function(response) {
            $log.error('Delete page failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.page.delete'), response);

            // revert page deletion on interface
            if (oldParent instanceof FrontofficeMenu) {
                oldParent.pages.push(page);
            }
            else if (oldParent instanceof FrontofficePage) {
                oldParent.childrenPages.push(page);
            }

            // reinsert page at its original display order
            page.setDisplayOrder(oldDisplayOrder-1);
        });
    };

    // Page hierarchy listener (page dropped on a menu or another page)
    $scope.$on('dropEvent', function(evt, dragged, droppedOn) {
        $log.log('Page dropEvent');

        // Check supported dragged type
        if (!(dragged instanceof FrontofficePage)) {
            $log.log("This drop event (page dropEvent) is not concerned by dragged element");
            return;
        }

        // Check supported droppedOn type
        if (!(droppedOn instanceof FrontofficeMenu || droppedOn instanceof FrontofficePage || undefined != droppedOn.insertAfterPage)) {
            $log.log("This drop event (page dropEvent) is not concerned by droppedOn element");
            return;
        }

        // Prevent elements from being dropped on themselves
        if (dragged == droppedOn) {
            $log.log("Element dropped on itself. Aborting.");
            return;
        }

        // Do nothing if the element has been dropped on its current parent
        if (dragged.parentElement == droppedOn) {
            $log.warn("Dropped element on its current parent. Do not update.");
            return;
        }

        if (droppedOn instanceof FrontofficePage && false == droppedOn.isChildPageAllowed) {
            $log.warn('The page "'+ droppedOn.name +'" cannot have children pages');
            NotificationService.addWarning(Translator.trans('notification.warning.page.%name%.cannot.have.children.pages', {'name': droppedOn.name}));
            $scope.$apply();
            return;
        }

        var targetParentElement;
        var targetDisplayOrder = null;
        if (droppedOn instanceof FrontofficeMenu) {
            targetParentElement = droppedOn;
        }
        else if (droppedOn instanceof FrontofficePage) {
            targetParentElement = droppedOn;
        }
        else {
            targetParentElement = droppedOn.insertAfterPage.parentElement;
            targetDisplayOrder = droppedOn.insertAfterPage.displayOrder
        }

        // Do nothing if the element has been dropped on its actual parent at the same order
        if (dragged.displayOrder == targetDisplayOrder && dragged.parentElement == targetParentElement) {
            $log.warn("Dropped element on its actual display order. Do not update.");
            return;
        }

        var oldParentElement = dragged.parentElement;
        var oldDisplayOrder = dragged.displayOrder;

        // If parent has changed
        if (oldParentElement != targetParentElement) {
            // Pre move element on the interface before calling server
            dragged.setParentElement(targetParentElement);
        }

        // If display order changed
        if (null != targetDisplayOrder) {
            dragged.setDisplayOrder(targetDisplayOrder);
        }

        var pageData = {
            id: dragged.id,
            displayOrder: dragged.displayOrder,
            parentPage: null,
            menu: null,
        };
        if (dragged.parentElement instanceof FrontofficeMenu) {
            pageData.menu = dragged.parentElement.id;
        }
        else if (dragged.parentElement instanceof FrontofficePage) {
            pageData.parentPage = dragged.parentElement.id;
        }

        FrontofficeSiteFactory.updatePage(pageData,'patch').then(function(response) {
            NotificationService.addSuccess(Translator.trans('notification.success.page.move'));
        }, function(response) {
            $log.error('Update page after drop failed: ', response);
            NotificationService.addError(Translator.trans('notification.error.page.move'), response);

            // Revert moving on interface
            dragged.setParentElement(oldParentElement);

            if (dragged.displayOrder >= oldDisplayOrder) oldDisplayOrder--;
            dragged.setDisplayOrder(oldDisplayOrder);
        });

        // Propagate promise resolution, update scope
        $scope.$apply();
    });

    if ($state.current.name == 'backoffice.frontoffice') {
        // at startup, if there is only one site, go to site's detail page by default
        if ($scope.sites.length == 1) $scope.openSite($scope.sites[0]);
        //at startup, if there is at least two sites, go to sites list page
        else $state.go('backoffice.frontoffice.list');
    }
}]);
