/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-21 17:21:58
 *
 ******************************************************************************
 *
 * Use this directive to include a partial without generating a new dom node
 *
 * Usage:
 *     <div class="theRealDivIWant">
 *         <div ng-include="myTemplateName" include-replace></div>
 *     </div>
 *
 *      Will output:
 *      <div class="theRealDivIWant">
 *         My template content
 *     </div>
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azIncludeReplace', function () {
    return {
        require: 'ngInclude',
        restrict: 'A',
        link: function (scope, el, attrs) {
            el.replaceWith(el.children());
        }
    };
});
