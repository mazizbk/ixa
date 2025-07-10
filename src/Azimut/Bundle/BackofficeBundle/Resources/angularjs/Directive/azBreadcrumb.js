/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-18 15:42:29
 *
 ******************************************************************************
 *
 * Breadcrumb component.
 * Needs azAdaptiveBreadcrumb directive
 *
 * Usage :
 *     <az-breadcrumb breadcrumb="myBreadcrumbObject" current-element="myCurrentElementObject" open-function="myOpenBreadcrumbElementFunction"></az-breadcrumb>
 *
 *
 * The breadcrumb object contains the sequence of parents :
 *
 *     myBreadcrumbObject = {
 *         elements: [
 *             {
 *                 id: 1,
 *                 name: 'My folder 1',
 *                 parentElement: {...}
 *             },
 *             {
 *                 id: 2,
 *                 name: 'My folder 2',
 *                 parentElement: {...}
 *             }
 *         ]
 *     }
 *
 * The "name" property can also be named "title"
 * Name can be a translated object : {en: 'an english name', fr: 'un nom français'}
 *
 * The "parentElement" property can also be named "parentFile"
 *
 * When clicking on a breadcrumb element, the given "openFunction" will be called with the current element (from the given breadcrumb model)
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azBreadcrumb', [
'$rootScope', '$timeout',
function($rootScope, $timeout) {
    return {
        restrict: 'E',
        scope: {
          breadcrumb: '=',
          openFunction: '=',
          currentElement: '='
        },
        link: function(scope, element, attrs)  {
            scope.locale = $rootScope.locale;

            scope.$watch('currentElement', function(currentElement) {
                if(undefined != currentElement) scope.parentElement = currentElement.parentElement || currentElement.parentFile;
            });

            scope.getBreadcrumbElementName = function(breadcrumbElement, locale) {
                var name = breadcrumbElement.name || breadcrumbElement.title;

                if (angular.isObject(name)) {
                    if (null != name[locale]) {
                        return name[locale];
                    }

                    for (var translatedLocale in name) {
                        if (null != name[translatedLocale]) {
                            return name[translatedLocale];
                        }
                    }

                    return '[' + breadcrumbElement.id + ']';
                }

                return name;
            }
        },
        template:
            '<a href ng-if="parentElement" ng-click="openFunction(parentElement)" class="breadcrumb-up-btn">'+
                '<span class="glyphicon glyphicon-folder-close"></span> '+
                '<span class="glyphicon glyphicon-arrow-up glyphicon-overlay"></span>'+
            '</a>'+
            '<ol class="breadcrumb" az-adaptive-breadcrumb ng-model="breadcrumb">'+
                '<li ng-show="breadcrumb.shrinked && !hideParentIndicator" class="inactive">...</li>'+
                '<li ng-repeat="breadcrumbElement in breadcrumb.elements" ng-class="{\'active\': $last}" ng-hide="breadcrumbElement.hide"><a ng-if="!$last" ng-click="openFunction(breadcrumbElement)">{{ getBreadcrumbElementName(breadcrumbElement, locale)|capitalize }}</a><span ng-if="$last">{{ getBreadcrumbElementName(breadcrumbElement, locale)|capitalize }}</span></li>'+
            '</ol>'
    }
}]);
