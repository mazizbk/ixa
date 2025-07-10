/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-24 14:55:29
 */

'use strict';
angular.module('azimutMediacenter.service')

.factory('MediacenterFile', [
'$log', 'ObjectExtra', 'ArrayExtra', 'StringExtra',
function($log, ObjectExtra, ArrayExtra, StringExtra) {
    $log = $log.getInstance('MediacenterFile');

    var MediacenterFile = function(fileData, parentFile) {
        // parent is determined by parentFile or fileData.parentFile, so remove the specific ones
        delete fileData.parentFolder;
        delete fileData.folder;

        angular.extend(this, fileData);

        this.metaData = {
            originalProperties: []
        }

        // list properties of fileData object and store them in metaData
        for(var fileProperty in fileData) {
            if('isCompleteObject' != fileProperty) this.metaData.originalProperties.push(fileProperty);
        }

        if(parentFile) this.parentFile = parentFile;

        // by default, we consider the object as a partial file
        if(undefined === this.isCompleteObject) this.isCompleteObject = false;

        this.fileType = MediacenterFile.guessFileType(this);

        // if the file is a folder
        if('folder' == this.fileType) {
            this.fileType = 'folder';
            this.cssIcon = 'glyphicon glyphicon-folder-open';
            this.showSubfolders = false;
            if(undefined == parentFile) {
                // translate root folders name
                this.name = Translator.trans(angular.lowercase(this.name));
                // expand root folders children by default
                this.showSubfolders = true;
            }
            if(undefined == this.medias) this.medias = [];
            if(undefined == this.subfolders) this.subfolders = [];



            // some properties must always be considered as originals
            ArrayExtra.merge(this.metaData.originalProperties, ['parentFolder']);

        }
        // if the file is a media
        else if('media' == this.fileType) {
            this.fileType = 'media';
            this.cssIcon = 'glyphicon glyphicon-file';

            if('image' == this.mediaType || 'video' == this.mediaType) this.thumb = this.path;

            if(undefined == this.declinations) this.declinations = [];

            // some properties must always be considered as originals
            ArrayExtra.merge(this.metaData.originalProperties, ['declinations']);
        }

        else {
            $log.error('Cannot create MediacenterFile, unable to determine file type');
        }

        this.refreshDate = new Date();

        return this;
    };

    /*MediacenterFile.prototype.setParentFile = function(parentFile) {
        this.parentFile = parentFile;

        if('folder' == this.fileType) {
            this.parentFolder = {
                id: parentFile.id,
                name: parentFile.name
            };
        }
        else {
            this.folder = {
                id: parentFile.id,
                name: parentFile.name
            };
        }

    }*/

    MediacenterFile.prototype.getPath = function() {
        return StringExtra.slugify(this.name);
    }

    MediacenterFile.prototype.getFullpath = function() {

        var fullpath = this.getPath();

        if(this.parentFile) fullpath = this.parentFile.getFullpath() +"/"+ fullpath;

        return fullpath;
    }

    // return a plain object containing only the original properties of the MediacenterFile
    MediacenterFile.prototype.toRawData = function () {
        var rawData = {};

        // keep only original properties
        for(var i=0; i<this.metaData.originalProperties.length; i++) {
            rawData[this.metaData.originalProperties[i]] = this[this.metaData.originalProperties[i]];
        }

        if('folder' == this.fileType) {

            // do not include folder subresources
            rawData = angular.copy(rawData);
            delete rawData.subfolders;
            delete rawData.medias;

            rawData.parentFolder = {
                id: this.parentFile.id,
                name: this.parentFile.name
            };
        }
        else {
            rawData.folder = {
                id: this.parentFile.id,
                name: this.parentFile.name
            };
        }

        // make a deepCopy because some children may be objects
        rawData = ObjectExtra.deepCopy(rawData);

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     *
     * API return type specifics fields directly in the media object wether form wait for
     * these fields to be into a subform named 'mediaType'. So the binding doesn't work for them.
     *
     * HACK: 'quick' solution, move all media specific fields to a mediaType subobject
     * EDIT: there is currently no way to change this because of Symfony Form component
     */

    MediacenterFile.prototype.toFormData = function () {
        var rawData = this.toRawData();
        var formData;

        if('folder' == this.fileType) {
            formData = {
                id: rawData.id,
                name: rawData.name,
                parentFolder: rawData.parentFolder?rawData.parentFolder.id:null
            }
        }
        else {

            formData = {
                id: rawData.id,
                name: rawData.name,
                description: rawData.description,
                folder: rawData.folder.id,
                type: rawData.mediaType,
                mediaType: rawData,
                mediaDeclinations: rawData.declinations,
            }

            delete formData.mediaType.id;
            delete formData.mediaType.name;
            delete formData.mediaType.description;
            delete formData.mediaType.folder;
            delete formData.mediaType.mediaType;
            delete formData.mediaType.thumb;
            delete formData.mediaType.path;
            delete formData.mediaType.mainDeclination;
            delete formData.mediaType.declinations;
            delete formData.mediaType.cssIcon;
            delete formData.mediaType.creationDate;

            // Remove shortcut properties on cmsfile
            if ('image' == this.mediaType) {
                delete formData.mediaType.pixelWidth;
                delete formData.mediaType.pixelHeight;
            }

            angular.forEach(formData.mediaDeclinations, function(declination, key) {
                formData.mediaDeclinations[key] = {
                    id: declination.id,
                    name: declination.name,
                    media: formData.id,
                    isMainDeclination: declination.isMainDeclination,
                    type: declination.mediaDeclinationType,
                    file: declination.path,
                    mediaDeclinationType: declination
                }

                delete formData.mediaDeclinations[key].mediaDeclinationType.id;
                delete formData.mediaDeclinations[key].mediaDeclinationType.name;
                delete formData.mediaDeclinations[key].mediaDeclinationType.media;
                delete formData.mediaDeclinations[key].mediaDeclinationType.mediaDeclinationType;
                delete formData.mediaDeclinations[key].mediaDeclinationType.isMainDeclination;
                delete formData.mediaDeclinations[key].mediaDeclinationType.resolution;

            });
        }

        return formData;
    }

    // static method
    MediacenterFile.guessFileType = function(file) {
        if (file.subfolders || file.medias) return 'folder';
        return 'media';
    }

    return MediacenterFile;
}]);
