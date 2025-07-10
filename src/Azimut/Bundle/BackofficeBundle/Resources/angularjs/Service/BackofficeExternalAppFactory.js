/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-09 15:37:04
  *
  ******************************************************************************
  *
  * Contains a list of external app definitions
  *
  */

'use strict';

angular.module('azimutBackoffice.service')

.factory('BackofficeExternalAppFactory', [
'$log', 'BackofficeMenuFactory', 'ArrayExtra',
function($log, BackofficeMenuFactory, ArrayExtra) {
    $log = $log.getInstance('BackofficeExternalFactory');

    var appDefinitions = [];

    return {
        getAppDefinitions: function() {
            return appDefinitions;
        },
        addAppDefinition: function(appDefinition) {
            if(!angular.isObject(appDefinition)) throw 'appDefinition has to be an Object';
            if(null == appDefinition.menuTitle) throw 'appDefinition must have a menuTitle attribute';
            if(null == appDefinition.shortName) throw 'appDefinition must have a shortName attribute';
            if(null == appDefinition.url) throw 'appDefinition must have an url attribute';

            appDefinitions.push(appDefinition);

            BackofficeMenuFactory.addMenuItem({
                title: appDefinition.menuTitle,
                icon: appDefinition.menuIcon,
                stateName: 'backoffice.external_app',
                stateParams: {
                    'appName': appDefinition.shortName
                },
                displayOrder: appDefinition.menuDisplayOrder,
            });
        },
        getAppDefinition: function(shortName) {
            return ArrayExtra.findFirstInArray(appDefinitions, {
                shortName: shortName
            });
        },
    }
}]);
