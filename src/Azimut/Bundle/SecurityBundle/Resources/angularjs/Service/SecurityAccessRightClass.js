/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-26 12:04:38
 */

'use strict';

angular.module('azimutSecurity.service')

.factory('SecurityAccessRightClass', [
'ObjectExtra', 'ArrayExtra',
function(ObjectExtra, ArrayExtra) {

    var SecurityAccessRightClass = function SecurityAccessRightClass(accessRightData) {

        this.id = accessRightData.id;
        this.userId = accessRightData.userId;
        this.groupId = accessRightData.groupId;
        this.class = accessRightData.class;
        this.roles = accessRightData.roles?accessRightData.roles:[];

        return this;
    };

    /**
     * return a plain object in the API format
     */
    SecurityAccessRightClass.prototype.toRawData = function () {
        var rawData = {
            id: this.id,
            type: 'class',
            userId: this.userId,
            groupId: this.groupId,
            accessRightType: {
                class: this.class,
                roles: this.roles
            }
        };

        return rawData;
    }

    /**
     * return a plain object to bind into the form
     */
    SecurityAccessRightClass.prototype.toFormData = function() {
        return this;
    }

    return SecurityAccessRightClass;
}]);
