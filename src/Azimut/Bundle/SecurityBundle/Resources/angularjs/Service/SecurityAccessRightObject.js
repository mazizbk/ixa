/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-09-17 15:04:38
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityAccessRightObject', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var  SecurityAccessRightObject = function SecurityAccessRightObject(accessRightData) {
        this.id = accessRightData.id;
        this.userId = accessRightData.userId;
        this.groupId = accessRightData.groupId;
        this.objectId = accessRightData.objectId;
        this.roles = accessRightData.roles?accessRightData.roles:[];
        this.type = accessRightData.accessRightType;

        return this;
    };

    /**
     * return a plain object in the API format
     */
    SecurityAccessRightObject.prototype.toRawData = function () {
        var rawData = {
            id: this.id,
            type: this.type,
            userId: this.userId,
            groupId: this.groupId,
            accessRightType: {
                roles: this.roles,
                objectId: this.objectId
            }
        };

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    SecurityAccessRightObject.prototype.toFormData = function() {
        return this;
    }

    return SecurityAccessRightObject;
}]);
