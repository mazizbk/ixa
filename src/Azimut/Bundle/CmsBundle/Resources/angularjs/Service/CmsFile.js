/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-09-20 15:53:42
 */

'use strict';

angular.module('azimutCms.service')

.factory('CmsFile', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var CmsFile = function CmsFile(cmsFileData) {
        angular.extend(this, cmsFileData);

        this.metaData = {
            originalProperties: []
        }

        // List properties of cmsFileData object and store them in metaData
        for(var dataProperty in cmsFileData) {
            if('isCompleteObject' != dataProperty) this.metaData.originalProperties.push(dataProperty);
        }

        // By default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        return this;
    };

    CmsFile.prototype.getName = function (locale) {
        if (angular.isObject(this.name)) {
            if (null != this.name[locale]) {
                return this.name[locale];
            }

            for (var translatedLocale in this.name) {
                if (null != this.name[translatedLocale]) {
                    return this.name[translatedLocale];
                }
            }

            // If the name of the CMS File is not set, display "[type id]"
            return '[' + Translator.transChoice('cms.file.type.' + this.cmsFileType, 1) + ' ' + this.id + ']';
        }
        return this.name;
    }

    CmsFile.prototype.getAbstract = function (locale) {
        if (angular.isObject(this.abstract)) {
            if (null != this.abstract[locale]) {
                return this.abstract[locale];
            }

            for (var translatedLocale in this.abstract) {
                if (null != this.abstract[translatedLocale]) {
                    return this.abstract[translatedLocale];
                }
            }

            return null;
        }
        return this.abstract;
    }
    /**
     * Return a plain object containing only the original properties of the CmsFile
     */
    CmsFile.prototype.toRawData = function () {
        var rawData = {};

        // Keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        delete rawData.menus;

        // Make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * Return a plain object to bind into the form
     */
    CmsFile.prototype.toFormData = function() {
        var formData = this.toRawData();

        // NB : getFile return file specifics fields directly in the media object wether form wait for these fields to be into a subform named 'fileType'. So the binding doesn't work for them.
        // HACK : 'quick' solution, move all file specific fields to a fileType subobject
        formData.type = this.cmsFileType;
        formData.cmsFileType = angular.copy(this);

        delete formData.cmsFileType.id;
        delete formData.cmsFileType.cmsFileType;
        delete formData.cmsFileType.mainAttachment;
        delete formData.cmsFileType.secondaryAttachments;
        delete formData.cmsFileType.complementaryAttachment1;
        delete formData.cmsFileType.complementaryAttachment2;
        delete formData.cmsFileType.complementaryAttachment3;
        delete formData.cmsFileType.complementaryAttachment4;
        delete formData.cmsFileType.relatedArticles;
        //END HACK

        // Keep only id for mediaDeclination in each attachments
        angular.forEach(formData.secondaryAttachments, function(attachment, key) {
            formData.secondaryAttachments[key].mediaDeclination = attachment.mediaDeclination.id;
        });
        if (undefined != formData.mainAttachment) {
            formData.mainAttachment.mediaDeclination = formData.mainAttachment.mediaDeclination.id;
        }
        if (undefined != formData.complementaryAttachment1) {
            formData.complementaryAttachment1.mediaDeclination = formData.complementaryAttachment1.mediaDeclination.id;
        }
        if (undefined != formData.complementaryAttachment2) {
            formData.complementaryAttachment2.mediaDeclination = formData.complementaryAttachment2.mediaDeclination.id;
        }
        if (undefined != formData.complementaryAttachment3) {
            formData.complementaryAttachment3.mediaDeclination = formData.complementaryAttachment3.mediaDeclination.id;
        }
        if (undefined != formData.complementaryAttachment4) {
            formData.complementaryAttachment4.mediaDeclination = formData.complementaryAttachment4.mediaDeclination.id;
        }

        return formData;
    }

    /**
     * Return a formatted object to bind in form infos
     */
    CmsFile.prototype.buildFormInfos = function() {
        var formInfos = {
            mainAttachment: angular.copy(this.mainAttachment),
            secondaryAttachments: angular.copy(this.secondaryAttachments),
            complementaryAttachment1: angular.copy(this.complementaryAttachment1),
            complementaryAttachment2: angular.copy(this.complementaryAttachment2),
            complementaryAttachment3: angular.copy(this.complementaryAttachment3),
            complementaryAttachment4: angular.copy(this.complementaryAttachment4),
            relatedArticles: angular.copy(this.relatedArticles)
        };

        if (undefined != formInfos.mainAttachment) {
            // Add media name in declination name for display
            formInfos.mainAttachment.mediaDeclination.name = formInfos.mainAttachment.mediaDeclination.media.name + ' (' + formInfos.mainAttachment.mediaDeclination.name + ')';
        }

        if (undefined != formInfos.complementaryAttachment1) {
            // Add media name in declination name for display
            formInfos.complementaryAttachment1.mediaDeclination.name = formInfos.complementaryAttachment1.mediaDeclination.media.name + ' (' + formInfos.complementaryAttachment1.mediaDeclination.name + ')';
        }
        if (undefined != formInfos.complementaryAttachment2) {
            // Add media name in declination name for display
            formInfos.complementaryAttachment2.mediaDeclination.name = formInfos.complementaryAttachment2.mediaDeclination.media.name + ' (' + formInfos.complementaryAttachment2.mediaDeclination.name + ')';
        }
        if (undefined != formInfos.complementaryAttachment3) {
            // Add media name in declination name for display
            formInfos.complementaryAttachment3.mediaDeclination.name = formInfos.complementaryAttachment3.mediaDeclination.media.name + ' (' + formInfos.complementaryAttachment3.mediaDeclination.name + ')';
        }
        if (undefined != formInfos.complementaryAttachment4) {
            // Add media name in declination name for display
            formInfos.complementaryAttachment4.mediaDeclination.name = formInfos.complementaryAttachment4.mediaDeclination.media.name + ' (' + formInfos.complementaryAttachment4.mediaDeclination.name + ')';
        }

        // Add media name in declination name for display in each secondary attachment
        angular.forEach(formInfos.secondaryAttachments, function(attachment, key) {
            attachment.mediaDeclination.name = attachment.mediaDeclination.media.name + ' (' + attachment.mediaDeclination.name + ')';
        });

        return formInfos;
    }

    return CmsFile;
}]);
