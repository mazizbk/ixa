/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-19 15:30:02
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeMenu', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var FrontofficeMenu = function FrontofficeMenu(menuData, parentElement) {

        // parent is determined by parentElement or menuData.parentElement, so remove the specific ones
        delete menuData.siteId;

        angular.extend(this, menuData);

        this.metaData = {
            originalProperties: []
        }

        // list properties of menuData object and store them in metaData
        for(var dataProperty in menuData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        if(parentElement) this.parentElement = parentElement;

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        if(undefined == this.pages) this.pages = [];

        return this;
    };

    FrontofficeMenu.prototype.getName = function (locale) {
        return this.name;
    };

    FrontofficeMenu.prototype.getChildrenPagesCount = function () {
        return this.pages.length;
    };

    FrontofficeMenu.prototype.addChildPage = function (page) {
        if (-1 == this.pages.indexOf(page)) {
            // Add page
            this.pages.push(page);
            // Move page to the bottom
            page.setDisplayOrder(this.pages.length);
        }

        return this;
    };

    FrontofficeMenu.prototype.removeChildPage = function (page) {
        if (-1 != this.pages.indexOf(page)) {
            // Move page to the bottom
            page.setDisplayOrder(this.pages.length);
            // Remove page
            this.pages.splice(this.pages.indexOf(page), 1);
            // Unset page display order
            page.setDisplayOrder(null);
        }

        return this;
    };

    // return a plain object containing only the original properties of the FrontofficeMenu
    FrontofficeMenu.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        rawData.siteId = this.parentElement.id;
        delete rawData.pages;

        // make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    FrontofficeMenu.prototype.toFormData = function() {
        var rawData = this.toRawData();
        var formData;

        formData = rawData;
        formData.site = rawData.siteId;
        delete formData.siteId;
        delete formData.pages;

        return formData;
    }

    return FrontofficeMenu;
}]);
