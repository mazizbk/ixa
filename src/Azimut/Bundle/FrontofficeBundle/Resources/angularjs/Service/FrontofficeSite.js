/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-24 16:47:55
 */

'use strict';

angular.module('azimutFrontoffice.service')

.factory('FrontofficeSite', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var FrontofficeSite = function FrontofficeSite(siteData) {

        angular.extend(this, siteData);

        this.metaData = {
            originalProperties: []
        }

        // list properties of siteData object and store them in metaData
        for(var dataProperty in siteData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        if(undefined == this.menus) this.menus = [];

        return this;
    };

    FrontofficeSite.prototype.getName = function (locale) {
        return this.name;
    }

    // return a plain object containing only the original properties of the FrontofficeSite
    FrontofficeSite.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        delete rawData.menus;

        // make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    FrontofficeSite.prototype.toFormData = function() {
        var rawData = this.toRawData();
        var formData;

        formData = rawData;
        formData.layout = formData.layout.id;

        delete formData.menus;
        delete formData.uri;

        return formData;
    }

    return FrontofficeSite;
}]);
