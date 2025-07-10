/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-19 15:35:56
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeZone', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var FrontofficeZone = function FrontofficeZone(zoneData, parentElement) {

        // parent is determined by parentElement or zoneData.parentElement, so remove the specific ones
        delete zoneData.pageId;

        angular.extend(this, zoneData);

        this.metaData = {
            originalProperties: []
        }

        // list properties of zoneData object and store them in metaData
        for(var dataProperty in zoneData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        if(parentElement) this.parentElement = parentElement;

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        return this;
    };

    FrontofficeZone.prototype.getName = function (locale) {
        return this.name;
    }

    // return a plain object containing only the original properties of the FrontofficeZone
    FrontofficeZone.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        rawData.pageId = this.parentElement.id;
        delete rawData.attachments;

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    FrontofficeZone.prototype.toFormData = function() {
        var rawData = this.toRawData();
        var formData;

        formData = {
            id: rawData.id,
            title: rawData.title
        };

        return formData;
    }

    return FrontofficeZone;
}]);
