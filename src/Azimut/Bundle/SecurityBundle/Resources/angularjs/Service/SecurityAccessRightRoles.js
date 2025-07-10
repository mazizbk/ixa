/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-09-15 10:20:48
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityAccessRightRoles', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var SecurityAccessRightRoles = function SecurityAccessRightRoles(accessRightData) {
        if(undefined != accessRightData) {
            this.id = accessRightData.id;
            this.userId = accessRightData.userId; //todo if it's a group right there is a group id
            this.groupId = accessRightData.groupId;
            this.roles = accessRightData.roles;
        }
        else {
            this.roles = [];
        }
        return this;
    };

    /**
     * return a plain object in the API format
     */
    SecurityAccessRightRoles.prototype.toRawData = function () {

        var rawData = {
            id: this.id,
            type: 'roles',
            userId: this.userId, //todo if it's a group right there is a group id
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
    SecurityAccessRightRoles.prototype.toFormData = function() {
        return this.roles;
    }

    return SecurityAccessRightRoles;
}]);
