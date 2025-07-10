/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-11-07 11:38:27
 *
 ******************************************************************************
 *
 * This directive works in addition to ng-click directive
 * It automatically asks for confirmation (shows a yes no dialog) before
 * executing the ngClick expression.
 *
 * An optional attibute az-confirm-click-condition can be set to define a condition
 * to trigger the confirm dialog (condition is an AngularJS expression)
 *
 * Usage:
 *
 * <a href ng-click="deleteFile(file)" az-confirm-click="Are you sure you want
 * to delete this file?">delete</a>
 *
 * <a href ng-click="deleteFile(file)" az-confirm-click="Are you sure you want
 * to delete this file?" az-confirm-click-condition="!file.locked">delete</a>
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azConfirmClick', [
'azConfirmModal',
function(azConfirmModal) {

    function link(scope, element, attrs) {

        var conditionExpression = attrs.azConfirmClickCondition;

        // set the confirm expression
        scope.confirmClick = function() {

            // block click event if given condition not met
            if(undefined != conditionExpression && !scope.$eval(conditionExpression)) return false;

            azConfirmModal(scope.$eval(attrs.azConfirmClick) || 'Are you sure?').result.then(function() {
                scope.$eval(attrs.actualNgClick);
            });

            // blocks click action
            return false;
        }
    }

    return {
        restrict: 'A',
        compile: function(element, attrs) {

            //element.attr('actual-ng-click', attrs.ngClick);
            attrs.actualNgClick = attrs.ngClick;

            // add the confirm expression to the ngClick expression
            attrs.ngClick = 'confirmClick() && ('+ attrs.ngClick +')';

            // prevent from compile recursions
            element.removeAttr('az-confirm-click');

            // redirect to link function
            return link;
        }
    };

}]);
