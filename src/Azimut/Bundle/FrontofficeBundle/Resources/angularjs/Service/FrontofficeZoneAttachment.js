/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-19 15:38:09
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeZoneAttachment', [
'ObjectExtra', 'ArrayExtra', 'CmsFile',
function(ObjectExtra, ArrayExtra, CmsFile) {

    var FrontofficeZoneAttachment = function FrontofficeZoneAttachment(zoneAttachmentData, parentElement) {

        // parent is determined by parentElement or zoneAttachmentData.parentElement, so remove the specific ones
        delete zoneAttachmentData.zoneId;

        angular.extend(this, zoneAttachmentData);

        this.cmsFile = new CmsFile(this.cmsFile);

        this.metaData = {
            originalProperties: []
        }

        // list properties of zoneAttachmentData object and store them in metaData
        for(var dataProperty in zoneAttachmentData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        if(parentElement) this.parentElement = parentElement;

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        return this;
    };

    FrontofficeZoneAttachment.prototype.getName = function (locale) {
        return this.cmsFile.getName(locale);
    }

    // return a plain object containing only the original properties of the FrontofficeZoneAttachment
    FrontofficeZoneAttachment.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        rawData.zoneId = this.parentElement.id;

        // make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    FrontofficeZoneAttachment.prototype.toFormData = function() {
        var rawData = this.toRawData();
        var formData;

        formData = rawData;
        formData.page = rawData.zoneId;
        delete formData.zoneId;

        return formData;
    }

    return FrontofficeZoneAttachment;
}]);
