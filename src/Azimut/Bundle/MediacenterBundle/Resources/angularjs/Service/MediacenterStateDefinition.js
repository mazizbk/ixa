/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-05-27 14:39:27
 *
 ******************************************************************************
 *
 * Set the states as constants, so they will be available in config function
 * These will be used in MediacenterStateProvider
 *
 */

'use strict';
angular.module('azimutMediacenter.service')

.constant('MediacenterStateDefinition', {
    main: {
        name: 'mediacenter',
        url: "/mediacenter",
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_main'),
        resolve: {
            fileFactoryInitPromise: function(MediacenterFileFactory){
                return MediacenterFileFactory.init();
            }
        },
        controller: 'MediacenterMainController'
    },
    trash_bin: {
        name: 'mediacenter.trash_bin',
        url: '/trash_bin',
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_trash_bin'),
        controller: 'MediacenterTrashBinController'
    },
    new_media: {
        name: 'mediacenter.new_media',
        url: '/{filePath:nonURIEncoded}/new_media_:mediaType',
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_new_media'),
        controller: 'MediacenterNewMediaController'
    },
    new_media_declination: {
        name: 'mediacenter.new_media_declination',
        url: '/{filePath:nonURIEncoded}/new_declination_:mediaDeclinationType',
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_new_media_declination'),
        controller: 'MediacenterNewMediaDeclinationController'
    },
    file_detail: {
        name: 'mediacenter.file_detail',
        url: '/{filePath:nonURIEncoded}',
        templateUrl: Routing.generate('azimut_mediacenter_backoffice_jsview_file_detail'),
        controller: 'MediacenterFileDetailController'
    },
    forbidden: {
        name: 'mediacenter_forbidden',
        url: '/mediacenter_forbidden',
        params: {
            appName: 'mediacenter',
        },
        templateUrl: Routing.generate('azimut_backoffice_backoffice_jsview_forbiddden_application'),
        controller: 'BackofficeForbiddenApplicationController'
    }
});
