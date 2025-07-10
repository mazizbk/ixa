/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2013-09-23
 */

'use strict';

//we declare submodules to be sure all is instanciated when calling app config function
angular.module('azimutMediacenter.controller', []);
angular.module('azimutMediacenter.directive', []);
angular.module('azimutMediacenter.service', []);
angular.module('azimutMediacenter.filter', []);

angular.module('azimutMediacenter', [
    'azimutBackoffice',
    'azimutMediacenter.controller',
    'azimutMediacenter.directive',
    'azimutMediacenter.service',
    'azimutMediacenter.filter',
    'ui.router',
    'ui.event',
])

.config([
'MediacenterStateProvider',
function(MediacenterStateProvider) {
    // due to dynamic widget states injections in other apps,
    // Mediacenter states are defined in Service/MediacenterStateDefinition

    MediacenterStateProvider.attachNativeStatesTo('backoffice');
}])

//this function is called before controllers
.run([
'BackofficeMenuFactory',
function(BackofficeMenuFactory) {
    BackofficeMenuFactory.addMenuItem({
        title: Translator.trans('mediacenter.app.name'),
        icon: 'glyphicon-book',
        stateName: 'backoffice.mediacenter',
        displayOrder: 2
    });
}])
;

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutMediacenter');
