/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-25 09:56:30
 *
 ******************************************************************************
 *
 * Provide an enhanced $log service
 *
 * Add time prefix to every message logged
 * Store each message in a log history (with a customizable size limit)
 * Optionnaly display class context name from wich it has been called
 *
 * Usage :
 *    As usual for time prefix
 *
 *    Provide a new wrapper instance of the $log service with current class name
 *    $log = $log.getInstance('MyClassController');
 *
 *    Set new history max length
 *    $log.setHistoryMaxLength(5000);
 *
 */

"use strict";

angular.module('azimutBackoffice.service')

.config(['$provide', function($provide) {

    $provide.decorator('$log', ['$delegate', function($delegate) {

        // store original $log methods
        var originalLog = {
            log: $delegate.log,
            info: $delegate.info,
            warn: $delegate.warn,
            debug: $delegate.debug,
            error: $delegate.error
        };

        $delegate.history = [];
        var historyMaxLength = 500;

        var bindLogFunction = function(logFunction, historyType, className) {
            className = (className !== undefined) ? className : "";

            var newLogFunction = function () {

                // get method arguments
                var args = Array.prototype.slice.call(arguments);

                // current time
                var dateTime = new Date();
                var time = ("0" + dateTime.getHours()).slice(-2) + ":" + ("0" + dateTime.getMinutes()).slice(-2) + ":" + ("0" + dateTime.getSeconds()).slice(-2) + ":" + ("0" + dateTime.getMilliseconds()).slice(-3);

                // add timestamp and class name
                args[0] = time + ' ' + className + args[0];

                // call original method with same arguments
                logFunction.apply($delegate, args);

                // add message to history
                /*$delegate.history.push({
                    type: historyType,
                    message: args.join()
                });*/
                $delegate.history.push(historyType +' - '+ args.join());

                if($delegate.history.length > historyMaxLength) {
                    $delegate.history.splice(0, $delegate.history.length - historyMaxLength);
                }
            };

            // needed to support angular-mocks
            newLogFunction.logs = [];

            return newLogFunction;
        };

        // create decorate object
        $delegate.log = bindLogFunction( $delegate.log, 'log' );
        $delegate.info = bindLogFunction( $delegate.info, 'info' );
        $delegate.warn = bindLogFunction( $delegate.warn, 'warn' );
        $delegate.debug = bindLogFunction( $delegate.debug, 'debug' );
        $delegate.error = bindLogFunction( $delegate.error, 'error' );
        $delegate.setHistoryMaxLength = function(length) {
            historyMaxLength = length;
        }

        $delegate.getInstance = function(className) {
            className = '['+ className +'] ';

            return {
                log : bindLogFunction( originalLog.log, 'log', className ),
                info : bindLogFunction( originalLog.info, 'info', className ),
                warn : bindLogFunction( originalLog.warn, 'warn', className ),
                debug : bindLogFunction( originalLog.debug, 'debug', className ),
                error : bindLogFunction( originalLog.error, 'error', className ),
                history: $delegate.history,
                setHistoryMaxLength: $delegate.setHistoryMaxLength
            };
        };

        return $delegate;
    }]);

}]);
