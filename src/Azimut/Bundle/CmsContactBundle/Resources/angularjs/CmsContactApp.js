/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-10-30 11:37:25
 */

'use strict';

angular.module('azimutCmsContact.controller', []);
angular.module('azimutCmsContact.directive', []);
angular.module('azimutCmsContact.service', []);
angular.module('azimutCmsContact.filter', []);

angular.module('azimutCmsContact', [
    'azimutBackoffice',
    'azimutCms',
    'azimutCmsContact.controller',
    'azimutCmsContact.directive',
    'azimutCmsContact.service',
    'azimutCmsContact.filter',
    'ui.router',
    'ui.event',
    'azimutCms'
])

.config([
'$stateProvider', 'CmsStateProvider',
function($stateProvider, CmsStateProvider) {

    $stateProvider

        //main state
        .state('backoffice.cmscontact', {
            url: "/contact",
            templateUrl: Routing.generate('azimut_cmscontact_backoffice_jsview_main'),
            resolve: {
                fileFactoryInitPromise: function(CmsFileFactory) {
                    return CmsFileFactory.init('CmsContact');
                }
            },
            controller: 'CmsContactMainController'
        })

        .state('backoffice.cmscontact.trash_bin', {
            url: '/trash_bin',
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_trash_bin'),
            controller: 'CmsContactTrashBinController'
        })

        .state('backoffice.cmscontact.edit_contact', {
            url: '/contact/edit_:file_id',
            params: {
                cmsFileType: 'contact',
            },
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_file_detail'),
            resolve: {
                baseStateName: function() {
                    return 'backoffice.cmscontact.edit_contact';
                }
            },
            controller: 'CmsContactContactEditController'
        })

        .state('backoffice.cmscontact.contact_detail', {
            url: '/contact/:file_id',
            params: {
                cmsFileType: 'contact',
            },
            templateUrl: Routing.generate('azimut_cmscontact_backoffice_jsview_contact_detail'),
            controller: 'CmsContactContactDetailController'
        })

        .state('backoffice.cmscontact.new_contact', {
            url: '/new_contact',
            params: {
                cmsFileType: 'contact',
            },
            templateUrl: Routing.generate('azimut_cms_backoffice_jsview_new_file'),
            controller: 'CmsContactNewContactController'
        })
    ;

    CmsStateProvider.attachCmsFileDetailSubstatesTo('backoffice.cmscontact.edit_contact');
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {

    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('cms_contact.app.name'),
        icon: 'glyphicon-pro glyphicon-pro-address-book',
        stateName: 'backoffice.cmscontact',
        displayOrder: 5
    });

}])

;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutCmsContact');
