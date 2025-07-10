/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-26 10:50:48
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityAccessRightApp', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var SecurityAccessRightApp = function SecurityAccessRightApp(accessRightData) {
        if(undefined != accessRightData) {
            this.id = accessRightData.id;
            this.userId = accessRightData.userId;
            this.groupId = accessRightData.groupId;
            this.roles = accessRightData.roles?accessRightData.roles:[];
        }
        else {
            this.roles = [];
        }
        return this;
    };

    /**
     * return a plain object in the API format
     */
    SecurityAccessRightApp.prototype.toRawData = function () {

        var rawData = {
            id: this.id,
            type: 'app_roles',
            userId: this.userId,
            groupId: this.groupId,
            accessRightType: {
                roles: []
            }
        };
        if(undefined != this.roles.length) {
            for (var i = this.roles.length - 1; i >= 0; i--) {
                rawData.accessRightType.roles.push(this.roles[i].id);
            }
        }

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    SecurityAccessRightApp.prototype.toFormData = function() {
        return this.roles;
    }

    return SecurityAccessRightApp;
}]);
