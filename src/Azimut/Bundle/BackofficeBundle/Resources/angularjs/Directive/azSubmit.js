/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-06 09:37:59
 *
 ******************************************************************************
 *
 * Comes in replacement of the built-in ngSubmit directive
 * Use it for advanced validation controls (it deactivates the default html validation in browser)
 *
 * Usage :
 *  - attach directive like ngSubmit:
 *    <form ... az-submit="mysubmitfunction()"
 *
 *  - in the form you can display validation helpers like so:
 *    <span class="form-help-error" ng-show="myform.$dirty && myform.myfield.$error.required">Required</span>
 *
 *  - you can style validation helpers in css:
 *    form.ng-dirty.ng-invalid {
 *      background: #ee0000;
 *    }
 *    input.ng-dirty.ng-invalid, textarea.ng-dirty.ng-invalid, select.ng-dirty.ng-invalid {
 *      border: 1px solid red;
 *    }
 *    .form-help-error {
 *      color: red;
 *   }
 *
 * An optional param can be set : az-submit-params
 * This contains the full config object for forms
 * It contains a boolean 'submitActive' that will be set to false for 1 sec when submit action is called
 * Use this for deactivating your submit button to prevent multiple submit calls
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azSubmit', [
'$log', '$parse', '$timeout',
function ($log, $parse, $timeout) {
    $log = $log.getInstance('azSubmit');

    return {
        restrict: 'A',
        require: 'form',
        link: function (scope, formElement, attributes, formController) {
            var submitFunction = $parse(attributes.azSubmit);
            var submitParams;

            //if optionnal param object has been provided, watch for it from scope
            if(undefined != attributes.azSubmitParams) {
                scope.$watch(attributes.azSubmitParams, function(newValue, oldValue) {
                    submitParams = newValue;

                    if (submitParams != undefined) {
                        // plug form controller on the submit param object
                        submitParams.formController = formController;
                    }
                });
            }
            //defaut param object
            else {
                submitParams = {
                    submitActive: true
                }
            }

            //disable browser's html validation
            formElement.attr('novalidate','novalidate');

            formElement.bind('submit', function (event) {
                if(!submitParams.submitActive) return;

                //deactivate submit for 2 sec
                scope.$apply(function() {
                    submitParams.submitActive = false;
                });
                var submitActiveTimer = $timeout(function() {
                    scope.$apply(function() {
                        submitParams.submitActive = true;
                    });
                }, 2000);

                //set form as altered when user hit submit, so validation will be shown
                scope.$apply(function() {
                    formController.$setDirty();
                });

                // if form is not valid cancel it.
                if (!formController.$valid) {
                    $log.log('Form has errors, canceled submit');
                    return false;
                }

                // if TinyMCE is set, call "SaveContent" event on all instances inside this form
                if (undefined != window.tinyMCE) {
                    angular.forEach(window.tinyMCE.editors, function(editor) {
                        if (formElement.is(editor.formElement)) {
                            editor.save();
                        }
                    });
                }

                // if form is valid, call the given submit function
                scope.$apply(function() {
                    var promise = submitFunction(scope, {$event:event});

                    // if a promise is set, undo the submitActive 2s timer and use a callback
                    if (null != promise) {
                        $timeout.cancel(submitActiveTimer);
                        promise.finally(function() {
                            submitParams.submitActive = true;
                        });
                    }
                });
            });
        }
    };
}]);
