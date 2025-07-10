/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:53:02
 *
 ******************************************************************************
 *
 * Service for handling notification feebacks to user
 *
 * Usage :
 * NotificationService.addError('my error message', [apiResponseObj, options]);
 *
 * If apiResponseObj is provided, system will look inside and display server error
 * message below the custom message.
 *
 * When a form validation response is detected, the list of fields errors will be
 * printed in notification message ("flaten", without hierarchy).
 * An optionnal parameter can be set to display a complementary error message in case
 * the server return an error by no message. Use this to display hint :
 *     options.messageIfNoServerData
 */

'use strict';

angular.module('azimutBackoffice.service')

.service('NotificationService', [
'$log', '$timeout',
function($log, $timeout) {

    $log = $log.getInstance('NotificationService');

    function flatenErrorMessages(errorData, name) {
        var messages = [];
        name = name ? Translator.trans(name) + ': ' : '';
        if (errorData.errors) {
            for(var i=0; i<errorData.errors.length;i++) {
                messages.push(name + errorData.errors[i].toLowerCase() + '\n');
            }
        }
        if (errorData.children) {
            for(var prop in errorData.children) {
                messages = messages.concat(flatenErrorMessages(errorData.children[prop], prop));
            }
        }
        return messages
    }

    this.notifications = [];

    this.addError = function(msg, response, options) {
        this.addNotification(msg, response, 'danger', 'exclamation-sign', options);
    };

    this.addCriticalError = function(msg, response, options) {
        if(!options) options = {};
        options.critical = true;
        this.addNotification(msg, response, 'danger', 'exclamation-sign', options);
    };

    this.addWarning = function(msg, response, options) {
        this.addNotification(msg, response, 'warning', 'warning-sign', options);
    };

    this.addSuccess = function(msg, response, options) {
        this.addNotification(msg, response, 'success', 'ok-sign', options);
    };

    this.addInfo = function (msg, response, options) {
        this.addNotification(msg, response, 'info', 'info-sign', options);
    };

    this.addNotification = function(message, response, type, icon, options) {
        if(!options) options = {};
        if(options.critical == undefined) options.critical = false;

        var serverMessages = [];
        var hintMessage = null;

        // get server error message from response object
        if(response && response.data) {
            if(response.data.error && response.data.error.message) {
                serverMessages.push(response.data.error.message);
            }
            else if('Validation Failed' == response.data.message) {
                if(response.data.errors) {
                    serverMessages = flatenErrorMessages(response.data.errors);
                }
            }
        }

        if(!serverMessages.length > 0 && options.messageIfNoServerData) hintMessage = options.messageIfNoServerData;

        var notification = {
            type: type,
            icon: icon,
            message: message,
            serverMessages: serverMessages,
            hintMessage: hintMessage,
            critical: options.critical,
            link: options.link,
            linkLabel: options.linkLabel,
        };

        this.notifications.push(notification);

        // auto-hide the notification if it's not an error
        if(notification.type != 'danger' && !options.sticky) {
            var service = this;

            var delay = 3000;
            if(notification.type == 'warning') delay = 6000;

            $timeout(function() {
                notification.hide = true;
            }, delay);
        }
    };

    this.clear = function() {
        $log.log('Clearing notifications');
        this.notifications = [];
    };

}]);
