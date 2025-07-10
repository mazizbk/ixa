/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 12:11:06
 */

'use strict';

angular.module('azimutModeration.controller', []);
angular.module('azimutModeration.directive', []);
angular.module('azimutModeration.service', []);
angular.module('azimutModeration.filter', []);

angular.module('azimutModeration', [
    'azimutBackoffice',
    'azimutModeration.controller',
    'azimutModeration.directive',
    'azimutModeration.service',
    'azimutModeration.filter',
    'ui.router',
    'ui.event'
])

.config([
'$stateProvider',
function($stateProvider) {
    $stateProvider
        .state('backoffice.moderation', {
            url: "/moderation",
            templateUrl: Routing.generate('azimut_moderation_backoffice_jsview_main'),
            resolve: {
                fileFactoryInitPromise: function(CmsFileBufferFactory){
                    return CmsFileBufferFactory.init();
                }
            },
            controller: 'ModerationMainController'
        })

        .state('backoffice.moderation.cms_file_buffer_list', {
            url: '/files_:type_:targetZoneId',
            templateUrl: Routing.generate('azimut_moderation_backoffice_jsview_cms_file_buffer_list'),
            controller: 'CmsFileBufferListController'
        })

        .state('backoffice.moderation.cms_file_buffer_detail', {
            url: '/files_:cmsFileBufferType/file_:id',
            templateUrl: Routing.generate('azimut_moderation_backoffice_jsview_cms_file_buffer_detail'),
            controller: 'CmsFileBufferDetailController'
        })

        .state('backoffice.moderation.comment_list', {
            url: '/comments',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_comment_list'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.moderation';
                }
            },
            controller: 'ModerationCommentListController'
        })

        .state('backoffice.moderation.comment_detail', {
            url: '/comments/:comment_id',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_comment_detail'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.moderation';
                }
            },
            controller: 'CmsCommentDetailController'
        })
    ;
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {
    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('moderation.app.name'),
        icon: 'glyphicon-pro glyphicon-pro-conversation',
        stateName: 'backoffice.moderation',
        displayOrder: 40
    });
}])
;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutModeration');
