/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-12 10:53:53
 *
 ******************************************************************************
 *
 * Displays and handles form locale selector
 *
 * Usage : <az-form-locale-selector ng-model="formLocale"></az-form-locale-selector>
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azFormLocaleSelector', [
'$rootScope',
function($rootScope) {
    return {
        restrict: 'E',
        require: 'ngModel',
        scope: {
            'model': '=ngModel',
        },
        link: function(scope, element, attrs)  {
            scope.locales = $rootScope.locales;

            scope.setFormLocale = function(locale) {
                scope.model = locale;
            };

            scope.allLabel = Translator.trans('all');
        },
        template:
            '<div class="btn-group">' +
            '    <a ng-repeat="locale in locales" href ng-click="setFormLocale(locale)" class="btn" ng-class="{true:\'btn-primary active\', false:\'btn-default\'}[locale == model]">{{ locale|uppercase }}</a>' +
            '    <a href ng-click="setFormLocale(null)" class="btn" ng-class="{true:\'btn-primary active\', false:\'btn-default\'}[null == model]">{{ allLabel }}</a>' +
            '</div>'
    }
}]);
