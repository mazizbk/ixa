/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:37:22
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeSiteFactory', [
'$log', '$http', '$rootScope', '$q', 'FrontofficeSite', 'FrontofficeMenu', 'FrontofficePage', 'FrontofficeZone', 'FrontofficeZoneAttachment', 'ObjectExtra', '$interval', 'ActivityMonitorService', '$state',
function($log, $http, $rootScope, $q, FrontofficeSite, FrontofficeMenu, FrontofficePage, FrontofficeZone, FrontofficeZoneAttachment, ObjectExtra, $interval, ActivityMonitorService, $state) {
    $log = $log.getInstance('FrontofficeSiteFactory');

    var factory = this;
    var refreshIntervalPromise = null;

    factory.initialized = false;
    factory.autoCacheRefreshDelay = 2; // in minutes
    factory.maxCacheAge = 2; // in minutes
    factory.refreshDate = null;

    factory.urlPrefix = 'azimut_frontoffice_api_';

    factory.isGrantedUser = false;

    factory.sites = [];

    factory.sitesIndex = [];
    factory.menusIndex = [];
    factory.pagesIndex = [];
    factory.zonesIndex = [];

    factory.availablePageTypes = {};

    /*** privates functions ***/

    factory.getSitesFromServer = function() {
        var promise = $http.get(Routing.generate(factory.urlPrefix+'get_sites')).then(function (response) {

            var refreshDate = new Date();

            // clear sites array object
            //for (var i=0; i<factory.sites.length; i++) delete factory.sites[i];

            // reset indexes
            //factory.sitesIndex = [];
            //factory.menusIndex = [];
            //factory.pagesIndex = [];

            //modify received datas
            for (var i=0; i<response.data.sites.length; i++) {
                //parse element (add element object methods and build indexes)
                factory.sites[i] = factory.parseSite(response.data.sites[i], null, refreshDate);
            }

            // clean indexes
            for (var i=0; i<factory.sitesIndex; i++) {
                if(factory.sitesIndex[i].refreshDate != refreshDate) {
                    delete factory.sitesIndex[i];
                }
            }
            for (var i=0; i<factory.menusIndex; i++) {
                if(factory.menusIndex[i].refreshDate != refreshDate) {
                    delete factory.menusIndex[i];
                }
            }
            for (var i=0; i<factory.pagesIndex; i++) {
                if(factory.pagesIndex[i].refreshDate != refreshDate) {
                    delete factory.pagesIndex[i];
                }
            }

            //when data fetched, return it to populate the promise
            return factory.sites;
        });

        return promise;
    }

    factory.refreshCache = function() {
        return factory.getSitesFromServer().then(function(response) {
            factory.refreshDate = new Date();
        });
    }

    factory.autoRefreshCache = function() {
        var currentDateTime = new Date();

        // do not update if browser page is hidden, or mediacenter hidden, or cache not old enought
        if(ActivityMonitorService.isDocumentHidden || !ActivityMonitorService.isUserActive || -1 == $state.current.name.indexOf('.frontoffice.') || ((currentDateTime - factory.refreshDate)/1000/60 < factory.maxCacheAge) ) {
            return false;
        }

        $log.info('Trigger auto cache refresh');

        return factory.refreshCache();
    }

    factory.parseSite = function(siteData, refreshDate) {
        var newSite = new FrontofficeSite(siteData);

        // find existing site in index if exists
        var site = factory.sitesIndex[siteData.id];

        // it is a new site
        if(undefined == site) {
            site = newSite;

            factory.sites.push(site);
        }

        // it is an update of an existing site
        else {
            var showChildren = site.showChildren;

            // copy new object properties into object in the index (clearing original object before)
            ObjectExtra.shallowCopy(newSite, site);

            // keep original showChildren property
            site.showChildren = showChildren;
        }

        //update index
        factory.sitesIndex[site.id] = site;

        // here menus are raw data, we strip them and then inject the parsed FrontofficeSite
        site.menus = [];

        //loop over hierarchy
        for(var i=0;i<siteData.menus.length;i++) {
            factory.parseMenu(siteData.menus[i], site, refreshDate);
        }

        if(refreshDate) site.refreshDate = refreshDate;

        return site;
    }

    factory.parseMenu = function(menuData, parentSite, refreshDate) {
        // if parentSite is not explicitely provided, fetch it from the menuData object
        if(undefined === parentSite) {
            if(!menuData.siteId) {
                $log.error("Menu must have a parent site");
                return false;
            }
            //TODO: what if menu site doesn't exists in index => create it or trigger cache refresh?
            if(!factory.sitesIndex[menuData.siteId]) $log.error('Site with id '+ menuData.siteId +' does not exist in index (TODO: create it)');
            parentSite = factory.sitesIndex[menuData.siteId];
        }

        var newMenu = new FrontofficeMenu(menuData, parentSite);

        // find existing menu in index if exists
        var menu = factory.menusIndex[menuData.id];

        var cachedMenuPages;

        // it is a new menu
        if(undefined == menu) {
            menu = newMenu;

            // add the new menu to menus in parent site object (if not existing)
            if(-1 == menu.parentElement.menus.indexOf(menu)) menu.parentElement.menus.push(menu);
        }

        // it is an update of an existing menu
        else {
            var oldParentSite = menu.parentElement;

            var showChildren = menu.showChildren;

            cachedMenuPages = menu.pages;

            // copy new object properties into object in the index (clearing original object before)
            ObjectExtra.shallowCopy(newMenu, menu);

            // keep original showChildren property
            menu.showChildren = showChildren;

            // update hierarchy linkage if it has been moved server side in the meantime

            // remove menu from old site
            if(oldParentSite != menu.parentElement) {
                oldParentSite.menus.splice(oldParentSite.menus.indexOf(menu), 1);
            }

            // add menu in new site
            if(-1 == menu.parentElement.menus.indexOf(menu)) menu.parentElement.menus.push(menu);
        }

        //update index
        factory.menusIndex[menu.id] = menu;

        // here pages are raw data, we strip them and then inject the parsed FrontofficeMenu
        menu.pages = [];

        // if pages not provided in data, recall them from index (if exist)
        if(undefined == menuData.pages) {
            if(undefined != cachedMenuPages) menu.pages = cachedMenuPages;
        }
        else {
            //loop over hierarchy
            for(var i=0;i<menuData.pages.length;i++) {
                factory.parsePage(menuData.pages[i], menu, refreshDate);
            }
        }

        if(refreshDate) menu.refreshDate = refreshDate;

        return menu;
    }

    factory.parsePage = function(pageData, parentElement, refreshDate) {
        // if parentElement is not explicitely provided, fetch it from the pageData object
        if(undefined === parentElement) {
            if(undefined == pageData.menuId && undefined == pageData.parentPageId) {
                $log.error("Page page must have a parent menu or page");
                return false;
            }

            //page is in a menu
            if(undefined != pageData.menuId) {
                //TODO: what if page menu doesn't exists in index => create it or trigger cache refresh?
                if(!factory.menusIndex[pageData.menuId]) $log.error('Menu with id '+ pageData.menuId +' does not exist in index (TODO: create it)');
                parentElement = factory.menusIndex[pageData.menuId];
            }
            //page is in another page
            else {
                //TODO: what if page parent page doesn't exists in index => create it or trigger cache refresh?
                if(!factory.pagesIndex[pageData.parentPageId]) $log.error('Page with id '+ pageData.parentPageId +' does not exist in index (TODO: create it)');
                parentElement = factory.pagesIndex[pageData.parentPageId];
            }
        }

        var newPage = new FrontofficePage(pageData, parentElement);

        // find existing page in index if exists
        var page = factory.pagesIndex[pageData.id];

        var cachedPageZones;
        var cachedPageSubpages;

        // it is a new page
        if(undefined == page) {
            page = newPage;

            // add the new page to pages in parent site object (if not existing)
            if(page.parentElement instanceof FrontofficePage) {
                if(-1 == page.parentElement.childrenPages.indexOf(page)) page.parentElement.childrenPages.push(page);
            }
            else {
                if(-1 == page.parentElement.pages.indexOf(page)) page.parentElement.pages.push(page);
            }
        }

        // it is an update of an existing page
        else {
            var oldParentElement = page.parentElement;

            var showChildren = page.showChildren;

            cachedPageSubpages = page.childrenPages;
            cachedPageZones = page.zones;

            // copy new object properties into object in the index (clearing original object before)
            ObjectExtra.shallowCopy(newPage, page);

            // keep original showChildren property
            page.showChildren = showChildren;

            // update hierarchy linkage if it has been moved server side in the meantime

            // remove page from old menu
            if(oldParentElement != page.parentElement) {
                if(page.parentElement instanceof FrontofficePage) {
                    oldParentElement.childrenPages.splice(oldParentElement.childrenPages.indexOf(page), 1);
                }
                else {
                    oldParentElement.pages.splice(oldParentElement.pages.indexOf(page), 1);
                }
            }

            // add page in new parent
            if(page.parentElement instanceof FrontofficePage) {
                if(-1 == page.parentElement.childrenPages.indexOf(page)) page.parentElement.childrenPages.push(page);
            }
            else {
                if(-1 == page.parentElement.pages.indexOf(page)) page.parentElement.pages.push(page);
            }
        }

        //update index
        $log.log('update pages index : ', page.id, page);
        factory.pagesIndex[page.id] = page;

        // here childrenPages are raw data, we strip them and then inject the parsed FrontofficePage
        page.childrenPages = [];

        // if childrenPages not provided in data, recall them from index (if exist)
        if(undefined == pageData.childrenPages) {
            if(undefined != cachedPageSubpages) page.childrenPages = cachedPageSubpages;
        }
        else {
            //loop over hierarchy
            for(var i=0;i<pageData.childrenPages.length;i++) {
                factory.parsePage(pageData.childrenPages[i], page, refreshDate);
            }
        }

        // here zones are raw data, we strip them and then inject the parsed FrontofficePage
        page.zones = [];

        // if zones not provided in data, recall them from index (if exist)
        if(undefined == pageData.zones) {
            if(undefined != cachedPageZones) page.zones = cachedPageZones;
        }
        else {
            //loop over hierarchy
            for(var i=0;i<pageData.zones.length;i++) {
                factory.parseZone(pageData.zones[i], page, refreshDate);
            }
        }

        if(refreshDate) page.refreshDate = refreshDate;

        return page;
    }

    factory.parseZone = function(zoneData, parentPage, refreshDate) {
        // if parentPage is not explicitely provided, fetch it from the zoneData object
        if(undefined === parentPage) {
            if(!zoneData.pageId) {
                $log.error("Zone must have a parent page");
                return false;
            }
            //TODO: what if zone page doesn't exists in index => create it or trigger cache refresh?
            if(!factory.pagesIndex[zoneData.pageId]) $log.error('Page with id '+ zoneData.pageId +' does not exist in index (TODO: create it)');
            parentPage = factory.pagesIndex[zoneData.pageId];
        }

        var newZone = new FrontofficeZone(zoneData, parentPage);

        // find existing zone in index if exists
        var zone = factory.zonesIndex[zoneData.id];

        var cachedZoneAttachments;

        // it is a new zone
        if(undefined == zone) {
            zone = newZone;

            // add the new zone to zones in parent site object (if not existing)
            if(-1 == zone.parentElement.zones.indexOf(zone)) zone.parentElement.zones.push(zone);
        }

        // it is an update of an existing zone
        else {
            var oldParentPage = zone.parentElement;

            cachedZoneAttachments = zone.attachments;

            // copy new object properties into object in the index (clearing original object before)
            ObjectExtra.shallowCopy(newZone, zone);

            // update hierarchy linkage if it has been moved server side in the meantime

            // remove zone from old page
            if(oldParentPage != zone.parentElement) {
                oldParentPage.zones.splice(oldParentPage.zones.indexOf(zone), 1);
            }

            // add zone in new page
            if(-1 == zone.parentElement.zones.indexOf(zone)) zone.parentElement.zones.push(zone);
        }

        //update index
        factory.zonesIndex[zone.id] = zone;

        // here attachments are raw data, we strip them and then inject the parsed FrontofficeZone
        zone.attachments = [];

        if(undefined == zoneData.attachments) {
            if (undefined != cachedZoneAttachments) zone.attachments = cachedZoneAttachments;
        }
        else {
            //loop over hierarchy
            for(var i=0;i<zoneData.attachments.length;i++) {
                factory.parseZoneAttachment(zoneData.attachments[i], zone, refreshDate);
            }
        }

        if(refreshDate) zone.refreshDate = refreshDate;

        return zone;
    }

    factory.parseZoneAttachment = function(zoneAttachmentData, parentZone) {
        // if parentZone is not explicitely provided, fetch it from the zoneAttachmentData object
        if(undefined === parentZone) {
            if(!zoneAttachmentData.zoneId) {
                $log.error("Zone attachment zoneAttachment must have a parent zone");
                return false;
            }
            //TODO: what if zone_attachment zone doesn't exists in index => create it or trigger cache refresh?
            if(!factory.zonesIndex[zoneAttachmentData.zoneId]) $log.error('Zone with id '+ zoneAttachmentData.zoneId +' does not exist in index (TODO: create it)');
            parentZone = factory.zonesIndex[zoneAttachmentData.zoneId];
        }

        var zoneAttachment = new FrontofficeZoneAttachment(zoneAttachmentData, parentZone);

        // add the attachment to attachments in parent zone object (if not existing)
        if(-1 == zoneAttachment.parentElement.attachments.indexOf(zoneAttachment)) zoneAttachment.parentElement.attachments.push(zoneAttachment);

        return zoneAttachment;
    }

    factory.getAvailablePageTypesFromServer = function() {
        return $http.get(Routing.generate(factory.urlPrefix+'get_page_availabletypes')).then(function (response) {
            var pageTypes = response.data.types;

            // translate type name
            for(var i=0;i<pageTypes.length;i++) {
                pageTypes[i].name = Translator.trans(pageTypes[i].id);
            }

            // clear actual value (without losing object reference)
            ObjectExtra.clear(factory.availablePageTypes);

            angular.extend(factory.availablePageTypes, pageTypes);
        });
    }

    /*** end privates functions ***/

    return {
        // init service (constructor)
        init: function() {
            var deferred = $q.defer();

            // if factory is already initialized, do not wait for data and refresh in background
            if(factory.initialized) {
                factory.refreshCache();
                deferred.resolve();
            }
            else {
                factory.refreshCache().then(function(response) {
                    factory.isGrantedUser = true;

                    factory.initialized = true;

                    // schedule auto cache refresh
                    $interval.cancel(refreshIntervalPromise);
                    refreshIntervalPromise = $interval(factory.autoRefreshCache, factory.autoCacheRefreshDelay*60*1000);

                    deferred.resolve();
                }, function(response) {
                    // if api access is forbidden or unauthorized
                    if(401 == response.data.error.code || 403 == response.data.error.code) {
                        factory.isGrantedUser = false;
                        // resolve instead of reject, instead this will be blocking, we want the controller to be called all the time so we can handle a redirect
                        deferred.resolve();
                    }
                    else {
                        deferred.reject(response);
                    }
                });
            }

            return deferred.promise;
        },

        pageTypes: function() {
            if (ObjectExtra.isEmpty(factory.availablePageTypes)) factory.getAvailablePageTypesFromServer();
            return factory.availablePageTypes;
        },

        sites: function() {
            return factory.sites;
        },

        getSites: function() {
            return factory.getSitesFromServer();
        },

        findSite: function(id) {
            return factory.sitesIndex[id];
        },

        findMenu: function(id) {
            return factory.menusIndex[id];
        },

        findPage: function(id) {
            return factory.pagesIndex[id];
        },

        findZone: function(id) {
            return factory.zonesIndex[id];
        },

        createSite: function(siteData) {
            var promise = $http.post(Routing.generate(factory.urlPrefix+'post_sites'), {site: siteData}).then(function (response) {
                var site = response.data.site;

                //parse element (add element object methods and build indexes)
                site = factory.parseSite(site, null);

                //factory.sites.push(site);

                response.data.site = site;

                return response;

            });

            return promise;
        },

        createPage: function(pageData) {
            var promise = $http.post(Routing.generate(factory.urlPrefix+'post_pages'), {page: pageData}).then(function (response) {
                var page = response.data.page;

                var parentElement = null;

                if(null != page.parentPageId) {
                    parentElement = factory.pagesIndex[page.parentPageId];
                }
                else if(null != page.menuId) {
                    parentElement = factory.menusIndex[page.menuId];
                }

                //parse element (add element object methods and build indexes)
                page = factory.parsePage(page, parentElement);

                response.data.page = page;

                /*if(null != page.parentPageId) {
                    parentElement.childrenPages.push(page);
                }
                else if(null != page.menuId) {
                    parentElement.pages.push(page);
                }*/

                return response;

            });

            return promise;
        },

        createZoneCmsFileAttachment: function(zoneCmsFileAttachmentData) {
            var promise = $http.post(Routing.generate(factory.urlPrefix+'post_zonecmsfileattachments'), {zone_cms_file_attachment: zoneCmsFileAttachmentData}).then(function (response) {

                // HTTP 204 no content = the attachment already exists
                if(204 !== response.status) {
                    var zoneCmsFileAttachment = response.data.zoneCmsFileAttachment;
                    var zone = factory.zonesIndex[zoneCmsFileAttachment.zoneId];

                    zoneCmsFileAttachment = factory.parseZoneAttachment(zoneCmsFileAttachment, zone);

                    response.data.zoneCmsFileAttachment = zoneCmsFileAttachment;
                }
                return response;
            });

            return promise;
        },

        getSite: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_site', {id: id})+'?locale='+locale).then(function (response) {

                var site = response.data.site;

                site.isCompleteObject = true;

                site = factory.parseSite(site);

                response.data.site = site;

                return response;
            });

            return promise;
        },

        getMenu: function(id) {
            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_menu', {id: id})).then(function (response) {
                var menu = response.data.menu;

                menu.isCompleteObject = true;

                menu = factory.parseMenu(menu);

                response.data.menu = menu;

                return response;
            });

            return promise;
        },

        getPage: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_page', {id: id})+'?locale='+locale).then(function (response) {
                var page = response.data.page;

                page.isCompleteObject = true;

                page = factory.parsePage(page);

                response.data.page = page;

                return response;
            });

            return promise;
        },

        getZone: function(id, locale) {
            if(null == locale) locale = $rootScope.locale;

            var promise = $http.get(Routing.generate(factory.urlPrefix+'get_zone', {id: id})+'?locale='+locale).then(function (response) {
                var zone = response.data.zone;

                zone.isCompleteObject = true;

                zone = factory.parseZone(zone);

                response.data.zone = zone;

                return response;
            });

            return promise;
        },

        updateSite: function(siteData) {
            var siteData = angular.copy(siteData);

            if('string' == typeof siteData.domainNames) siteData.domainNames = siteData.domainNames.split(',');

            var siteId = siteData.id;
            delete siteData.id;

            var promise = $http.put(Routing.generate(factory.urlPrefix+'put_site', {id: siteId}),{site: siteData}).then(function (response) {
                var site = response.data.site;

                site.isCompleteObject = true;

                site = factory.parseSite(site);

                response.data.site = site;

                return response;
            });

            return promise;
        },

        updateMenu: function(menuData) {
            var menuId = menuData.id;
            delete menuData.id;

            var promise = $http.put(Routing.generate(factory.urlPrefix+'put_menu', {id: menuId}),{menu: menuData}).then(function (response) {
                var menu = response.data.menu;

                menu.isCompleteObject = true;

                menu = factory.parseMenu(menu);

                response.data.menu = menu;

                return response;
            });

            return promise;
        },

        updatePage: function(pageData, method) {
            var promise = null;

            if (method == 'patch') {
                // Work on a simple copy of page
                var pagePatch = {
                    title: pageData.title,
                    parentPage: pageData.parentPage,
                    menu: pageData.menu,
                    displayOrder: pageData.displayOrder
                }

                // Do not send title if is has only one locale (not an object or array)
                if (angular.isString(pagePatch.title)) {
                    delete pagePatch.title;
                }

                promise = $http({
                    method: 'PATCH',
                    url: Routing.generate(factory.urlPrefix+'patch_page',{ id: pageData.id }),
                    data: { page: pagePatch }
                });
            }
            else {
                // Work on a copy of page

                // Temporary remove pages children because it will trigger a max call stack exception during deepCopy
                /*var tmpChildrenPages = page.pageType.childrenPages;
                delete page.pageType.childrenPages;*/

                var pagePut = {
                    menuTitle: pageData.menuTitle,
                    pageTitle: pageData.pageTitle,
                    pageSubtitle: pageData.pageSubtitle,
                    metaTitle: pageData.metaTitle,
                    differentPageTitle: pageData.differentPageTitle,
                    metaDescription: pageData.metaDescription,
                    slug: pageData.slug,
                    active: pageData.active,
                    metaNoIndex: pageData.metaNoIndex,
                    autoSlug: pageData.autoSlug,
                    autoMetas: pageData.autoMetas,
                    showInMenu: pageData.showInMenu,
                    parentPage: pageData.parentPage,
                    menu: pageData.menu,
                    pageType: ObjectExtra.deepCopy(pageData.pageType),
                    redirections: pageData.redirections,
                    displayOrder: pageData.displayOrder,
                    userRoles: pageData.userRoles,
                    uniquePasswordAccess: pageData.uniquePasswordAccess,
                    isPageParametersLocked: pageData.isPageParametersLocked,
                };

                /*
                page.pageType.childrenPages = tmpChildrenPages;
                */

                promise = $http.put(Routing.generate(factory.urlPrefix+'put_page', {id: pageData.id}),{page: pagePut});
            }

            promise.then(function (response) {
                var page = response.data.page;

                page.isCompleteObject = true;

                page = factory.parsePage(page);

                // update response
                response.data.page = page;

                return response;
            });

            return promise;
        },

        updateZone: function(zoneData) {
            // copy data to not alter input object
            var zonePutData = angular.copy(zoneData);

            var zoneId = zonePutData.id;
            delete zonePutData.id;

            var promise = $http.put(Routing.generate(factory.urlPrefix+'put_zone', {id: zoneId}),{zone: zonePutData}).then(function (response) {
                var zone = response.data.zone;

                zone = factory.parseZone(zone);
                zone.isCompleteObject = true;

                // update response
                response.data.zone = zone;

                return response;
            });

            return promise;
        },

        updateZoneCmsFileAttachment: function(zoneCmsFileAttachmentData) {
            var zoneCmsFileAttachmentPatch = {
                displayOrder: zoneCmsFileAttachmentData.displayOrder
            }

            var promise = $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix+'patch_zonecmsfileattachment',{ id: zoneCmsFileAttachmentData.id }),
                data: { zone_cms_file_attachment: zoneCmsFileAttachmentPatch }
            })

            .then(function (response) {

                return response;
            });

            return promise;
        },

        deleteSite: function(site) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_site',{ id: site.id })).then(function (response) {

                factory.sites.splice(factory.sites.indexOf(site), 1);

                delete factory.sitesIndex[site.id];

                return response;
            });

            return promise;
        },

        deletePage: function(page) {
            var promise = $http.delete(Routing.generate(factory.urlPrefix+'delete_page',{ id: page.id })).then(function (response) {
                //update pages list in parent menu or page
                if (page.parentElement) {
                    if (page.parentElement instanceof FrontofficeMenu) page.parentElement.pages.splice(page.parentElement.pages.indexOf(page), 1);
                    else if(page.parentElement instanceof FrontofficePage) page.parentElement.childrenPages.splice(page.parentElement.childrenPages.indexOf(page), 1);
                }

                delete factory.pagesIndex[page.id];

                return response;
            });

            return promise;
        },

        deleteZoneCmsFileAttachment: function(zoneCmsFileAttachment) {
            return $http.delete(Routing.generate(factory.urlPrefix+'delete_zonecmsfileattachment',{ id: zoneCmsFileAttachment.id }));
        },

        isGrantedUser: function() {
            return factory.isGrantedUser;
        },

        refreshCache: function() {
            return factory.refreshCache();
        }
    }
}]);
