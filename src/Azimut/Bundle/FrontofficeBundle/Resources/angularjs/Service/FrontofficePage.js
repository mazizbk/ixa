/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-19 15:32:38
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficePage', [
'ObjectExtra', 'ArrayExtra', 'FrontofficeMenu',
function(ObjectExtra, ArrayExtra, FrontofficeMenu) {

    var FrontofficePage = function FrontofficePage(pageData, parentElement) {
        // parent is determined by parentElement or pageData.parentElement, so remove the specific ones
        delete pageData.menuId;
        delete pageData.parentPageId;

        if ('link' == pageData.pageType) {
            if (undefined !== pageData.url) {
                pageData.linkType = 'external';
            }
            else if (undefined !== pageData.targetPageId) {
                pageData.linkType = 'internal';
            }
        }

        angular.extend(this, pageData);

        this.metaData = {
            originalProperties: []
        };

        // list properties of pageData object and store them in metaData
        for(var dataProperty in pageData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        if(parentElement) this.parentElement = parentElement;

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        if(undefined == this.childrenPages) this.childrenPages = [];
        if(undefined == this.zones) this.zones = [];

        this.showChildren = false;
        if(undefined != pageData.showChildren) this.showChildren = pageData.showChildren;

        return this;
    };

    // return a plain object containing only the original properties of the FrontofficePage
    // without subresources
    FrontofficePage.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        if(this.parentElement instanceof FrontofficeMenu) {
            rawData.menuId = this.parentElement.id;
        }
        else {
            rawData.parentPageId = this.parentElement.id;
        }

        delete rawData.zones;
        delete rawData.childrenPages;

        // make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     *
     * API return type specifics fields directly in the media object wether form wait for
     * these fields to be into a subform named 'pageType'. So the binding doesn't work for them.
     *
     * HACK: 'quick' solution, move all page specific fields to a pageType subobject
     */
    FrontofficePage.prototype.toFormData = function() {
        var rawData = this.toRawData();
        var formData = {
            id: rawData.id,
            menuTitle: rawData.menuTitle,
            pageTitle: rawData.pageTitle,
            pageSubtitle: rawData.pageSubtitle,
            differentPageTitle: rawData.differentPageTitle,
            slug: rawData.slug,
            autoSlug: rawData.autoSlug,
            autoMetas: rawData.autoMetas,
            metaTitle: rawData.metaTitle,
            metaDescription: rawData.metaDescription,
            showInMenu: rawData.showInMenu,
            active: rawData.active,
            metaNoIndex: rawData.metaNoIndex,
            parentPage: rawData.parentPageId,
            menu: rawData.menuId,
            type: rawData.pageType,
            displayOrder: rawData.displayOrder,
            redirections: rawData.redirections,
            userRoles: rawData.userRoles,
            uniquePasswordAccess: rawData.uniquePasswordAccess,
            isPageParametersLocked: rawData.isPageParametersLocked,
            pageType: rawData
        };

        if ('content' == this.pageType) {
            formData.pageType.layout = formData.pageType.layout.id;
        }

        else if ('alias' == this.pageType) {
            formData.pageType.pageContent = formData.pageType.pageContentId;
            delete formData.pageType.pageContentId;
        }

        else if ('link' == this.pageType) {
            formData.pageType.targetPage = formData.pageType.targetPageId;
            delete formData.pageType.targetPageId;
        }

        if ('link' == this.pageType || 'placeholder' == this.pageType) {
            delete formData.pageSubtitle;
            delete formData.autoMetas;
            delete formData.metaTitle;
            delete formData.metaDescription;
            delete formData.redirections;
            delete formData.metaNoIndex;
            delete formData.pageTitle;
            delete formData.differentPageTitle;
        }

        if (true == formData.autoSlug) {
            delete formData.slug;
        }
        if (true == formData.autoMetas) {
            delete formData.metaTitle;
            delete formData.metaDescription;
        }

        delete formData.pageType.id;
        delete formData.pageType.active;
        delete formData.pageType.metaNoIndex;
        delete formData.pageType.menuTitle;
        delete formData.pageType.pageTitle;
        delete formData.pageType.differentPageTitle;
        delete formData.pageType.pageSubtitle;
        delete formData.pageType.metaTitle;
        delete formData.pageType.metaDescription;
        delete formData.pageType.autoSlug;
        delete formData.pageType.autoMetas;
        delete formData.pageType.showInMenu;
        delete formData.pageType.slug;
        delete formData.pageType.parentPageId;
        delete formData.pageType.menuId;
        //delete formData.pageType.zones;
        delete formData.pageType.childrenPages;
        delete formData.pageType.zones;
        delete formData.pageType.pageType;
        delete formData.pageType.siteUri;
        delete formData.pageType.fullSlug;
        delete formData.pageType.redirections;
        delete formData.pageType.name;
        delete formData.pageType.isFullPageCmsFile;
        delete formData.pageType.fullPageCmsFileId;
        delete formData.pageType.isFullZoneCmsFile;
        delete formData.pageType.fullZoneCmsFileId;
        delete formData.pageType.isMonoZone;
        delete formData.pageType.monoZoneId;
        delete formData.pageType.displayOrder;
        delete formData.pageType.isChildPageAllowed;
        delete formData.pageType.userRoles;
        delete formData.pageType.uniquePasswordAccess;
        delete formData.pageType.isPageParametersLocked;

        return formData;
    };

    FrontofficePage.prototype.getChildPagesCount = function () {
        return this.childrenPages.length;
    };

    FrontofficePage.prototype.addChildPage = function (page) {
        if (-1 == this.childrenPages.indexOf(page)) {
            // Add child page
            this.childrenPages.push(page);
            // Move child page to the bottom
            page.setDisplayOrder(this.childrenPages.length);
        }

        return this;
    };

    FrontofficePage.prototype.removeChildPage = function (page) {
        if (-1 != this.childrenPages.indexOf(page)) {
            // Move child page to the bottom
            page.setDisplayOrder(this.childrenPages.length);
            // Remove child page
            this.childrenPages.splice(this.childrenPages.indexOf(page), 1);
            // Unset page display order
            page.setDisplayOrder(null);
        }

        return this;
    };

    FrontofficePage.prototype.setParentElement = function(newParentElement) {
        if (newParentElement == this.parentElement) {
            return this;
        }

        if (null != this.parentElement) {
            // Remove the page from old parent
            this.parentElement.removeChildPage(this);
        }

        newParentElement.addChildPage(this);

        this.parentElement = newParentElement;

        return this;
    };

    FrontofficePage.prototype.setDisplayOrder = function(newDisplayOrder) {
        if (null == this.displayOrder || null == newDisplayOrder) {
            this.displayOrder = newDisplayOrder;
            return this;
        }

        // update order (only section between old and new displayOrder)
        var startDisplayOrder = this.displayOrder;
        var endDisplayOrder = newDisplayOrder;

        var parentPagesCollection = null;
        if (this.parentElement instanceof FrontofficeMenu) {
            parentPagesCollection = this.parentElement.pages;
        }
        else {
            parentPagesCollection = this.parentElement.childrenPages;
        }

        // displayOrder has been increased
        if (endDisplayOrder > startDisplayOrder) {
            for(var i=startDisplayOrder+1; i<=endDisplayOrder; i++) {
                var pagePropagate = ArrayExtra.findFirstInArray(parentPagesCollection, {'displayOrder': i});

                // if the next display order does not exist then we reach the limit, we will insert element here
                if (undefined == pagePropagate) {
                    newDisplayOrder = i-1;
                    break;
                }
                else pagePropagate.displayOrder --;
            }
        }
        // displayOrder has been decreased
        else {
            newDisplayOrder++;
            endDisplayOrder = newDisplayOrder;

            for(var i=startDisplayOrder-1; i>=endDisplayOrder; i--) {

                var pagePropagate = ArrayExtra.findFirstInArray(parentPagesCollection, {'displayOrder': i});

                // if the next display order does not exist then we reach the limit, we will insert element here
                if (undefined == pagePropagate) {
                    newDisplayOrder = i+1;
                    break;
                }
                else pagePropagate.displayOrder ++;
            }
        }

        this.displayOrder = newDisplayOrder;

        return this;
    };

    return FrontofficePage;
}]);
