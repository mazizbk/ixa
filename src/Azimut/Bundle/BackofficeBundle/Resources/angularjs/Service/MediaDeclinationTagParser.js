/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-27 14:23:07
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('MediaDeclinationTagParser', [
'$log', 'MediacenterFileFactory', '$q',
function($log, MediacenterFileFactory, $q) {

    var parser = this;

    $log = $log.getInstance('MediaDeclinationTagParser');

    /**
     * ex: MediaDeclinationTagParser.tagDefinitionToHtml({id: 4, name: 'my image' thumb: 'myImg.jpg'}, 'm')
     */
    parser.tagDefinitionToHtml = function(tagObj, thumbSize, options) {
        options = angular.fromJson(options);
		if(options == undefined ){
            options = [];
        }
        if ('image' == tagObj.mediaType) {
            return '<img src="'+ Routing.generate('azimut_mediacenter_backoffice_file_proxy_thumb',{ filepath: tagObj.path, size: thumbSize }) +'" alt="'+ tagObj.name +'" data-media-declination="' + tagObj.id + '" '+ parser.getTagObjAttributes(tagObj, options) +' data-media-declination-type="image" />';
        }
        else if ('video' == tagObj.mediaType) {

            //var imgSrc = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            var imgSrc = '/img/backoffice/video-placeholder.png';

            if (undefined != tagObj.path) {
                imgSrc = Routing.generate('azimut_mediacenter_backoffice_file_proxy_thumb',{ filepath: tagObj.path, size: thumbSize });
            }

            return '<img src="'+ imgSrc +'" alt="'+ tagObj.name +'" data-media-declination="' + tagObj.id + '" '+ parser.getTagObjAttributes(tagObj, options) +' data-media-declination-type="video" />';
        }
        else if ('generic_embed_html' == tagObj.mediaType) {
            options['class'] = 'otherEmbedHtmlImg' + (options['class'] ? '' + options['class'] : '');
            return '<img src="/img/backoffice/video-placeholder.png" alt="'+ tagObj.name +'" data-media-declination="' + tagObj.id + '" '+ parser.getTagObjAttributes(tagObj, options) +' data-media-declination-type="generic_embed_html" />';
        }
        else {
            return '<em data-media-declination="' + tagObj.id + '" contenteditable="false"><span class="glyphicon glyphicon-'+tagObj.cssIcon+'" data-media-declination-type="other"></span> '+ tagObj.name +'</em>';
        }
    };

    parser.getTagObjAttributes = function(tagObj, options) {
        var attributes = '';

        if (undefined != options) {
            if (options.width) attributes = attributes + 'width="'+ options.width +'" ';
            if (options.height) attributes = attributes + 'height="'+ options.height +'" ';
            if (options.style) attributes = attributes + 'style="'+ options.style +'" ';
            if (options.class) attributes = attributes + 'class="'+ options.class +'" ';
        }

        return attributes;
    };

    return {

        tagDefinitionToHtml: parser.tagDefinitionToHtml,

        /**
         * Convert all text tags to html
         */
        tagsInTextToHtml: function(text, thumbSize) {

            var deferred = $q.defer();
            var promise = deferred.promise;

            // if no text, resolve the promise to return without waiting
            if (undefined == text) deferred.resolve(text);

            var regex = /## media-declination-(\d+)( \| (.*?))? ##/g;

            var promises = [];

            // replace work only with sync data, so we tweak it (it return original match, and do the real replace)
            text.replace(regex, function(match, mediaDeclinationId, optionalCapture, options, offset, string) {
                // fetch declination information for API (type, path, etc.)
                // TODO: this creates overhead, find a way to move it entirely on backend
                var promise = MediacenterFileFactory.getMediaDeclination(mediaDeclinationId).then(function(response) {

                    var mediaDeclination = response.data.mediaDeclination;

                    var mediaDeclinationName = mediaDeclination.media.name;
                    if (mediaDeclination.media.name != mediaDeclination.name) mediaDeclination.media.name + ' - ' + mediaDeclination.name;

                    var tagObj = {
                        id: mediaDeclination.id,
                        name: mediaDeclinationName,
                        path: mediaDeclination.path,
                        mediaType: mediaDeclination.mediaDeclinationType,
                        cssIcon: mediaDeclination.media.cssIcon
                    };

                    // replace tag whith parsed calue
                    text = text.replace(match, parser.tagDefinitionToHtml(tagObj, thumbSize, options));

                    // no need to return anything, as we don't get the output due to async
                    return null;
                }, function(response) {
                    if (404 == response.data.error.code) {
                        $log.warn('Media declination tag : declination not found, removing tag.');

                        // remove the tag
                        text = text.replace(match, '');
                    }
                });

                promises.push(promise);

                // no need to return anything, as we don't get the output due to async
                return null;
            });

            // if no tags found in text, resolve the promise
            if (0 == promises.length) {
                deferred.resolve(text);
                return promise;
            }

            // return parsed text when all tags transformed
            return $q.all(promises).then(function() {
                return text;
            });
        },

        htmlTagsToText: function(text) {

            if (undefined == text) return;

            // convert image tags
            var regex = /<img[^>]+data-media-declination="?([^"\s]+)"?[^>]*\/>/g;

            text = text.replace(regex, function(match, mediaDeclinationId, offset, string) {
                var domElement = $(match);

                var tagOptions = {
                    width: domElement[0].getAttribute('width'),
                    height: domElement[0].getAttribute('height'),
                    style: domElement[0].getAttribute('style'),
                    class: domElement[0].getAttribute('class') ? domElement[0].getAttribute('class').replace('videoImg ', '').replace('otherEmbedHtmlImg ', '') : null,
                };

                if (null == tagOptions.width) delete tagOptions.width;
                if (null == tagOptions.height) delete tagOptions.height;
                if (null == tagOptions.style) delete tagOptions.style;
                if (null == tagOptions.class) delete tagOptions.class;

                return '## media-declination-'+ mediaDeclinationId +' | '+ angular.toJson(tagOptions) +' ##';
            });


            // convert tags other than image
            regex = /<em[^>]+data-media-declination="?([^"\s]+)"?[^>]*>.*<\/em>/g;

            text = text.replace(regex, function(match, mediaDeclinationId, offset, string) {
                return '## media-declination-'+ mediaDeclinationId +' ##';
            });

            return text;
        }
    }
}]);
