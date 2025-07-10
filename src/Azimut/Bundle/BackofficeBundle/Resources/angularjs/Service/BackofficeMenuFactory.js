/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-05-02 16:04:05
 *
 ******************************************************************************
 *
 * Menu factory : contains the backoffice main app menu
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('BackofficeMenuFactory', [
'$log',
function($log) {
    $log = $log.getInstance('BackofficeMenuFactory');

    var menu = [];

    return {
        getMenu: function() {
            return menu;
        },
        //menuItem attributes : title, icon, stateName, stateParams, displayOrder
        addMenuItem: function(menuItem) {
            if(!angular.isObject(menuItem)) throw 'menuItem has to be an Object';
            if(null == menuItem.title) throw 'menuItem must have a title attribute';

            menu.push(menuItem);
        }
    }
}]);
