/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2014-01-10 16:39:05
 */

'use strict';

angular.module('azimutDemoExternalAppApp', ['azimutBackoffice']).run([
'BackofficeExternalAppFactory',
function(BackofficeExternalAppFactory) {
    BackofficeExternalAppFactory.addAppDefinition({
        menuTitle: Translator.trans('demo_external.app.name'),
        menuIcon: 'glyphicon-info-sign',
        menuDisplayOrder: 10,
        shortName: 'demo_external_app',
        url: Routing.generate('azimut_demo_external_app'),
    });
}]);

//inject dependency into backoffice main app
angular.module('azimutBackoffice').requires.push('azimutDemoExternalAppApp');
