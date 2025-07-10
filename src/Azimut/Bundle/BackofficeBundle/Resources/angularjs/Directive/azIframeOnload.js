/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-21 11:34:50
  *
  ******************************************************************************
  *
  * Apply loading class to iframe's parent on iframe url change
  * Plug a callback on Iframe onLoad event
  * Directive's parameter is a callback function
  *
  * Usage:
  *     <iframe src="..." az-iframe-onload></iframe>
  *     <iframe src="..." az-iframe-onload="onIframeLoad"></iframe>
  *
  * function onIframeLoad has to be defined in the scope of the controller
  *
  * This parameters will be given to the callback :
  *     - url: url of the iframe
  *     - title: title on the iframe's document
  *     - element: iframe DOM element
  */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azIframeOnload',
function() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var callbackFunction = scope.$eval(attrs.azIframeOnload);

            if (null != callbackFunction && !angular.isFunction(callbackFunction)) {
                throw 'azIframeOnload parameter must be a function';
            }

            element.parent().addClass('loading');

            element[0].onload = function() {
                element.parent().removeClass('loading');

                if (null != callbackFunction) {
                    callbackFunction(this.contentWindow.location.href, this.contentDocument.title, this);
                }

                element[0].contentWindow.onbeforeunload = function() {
                    element.parent().addClass('loading');
                };
            }
        }
    };
});
