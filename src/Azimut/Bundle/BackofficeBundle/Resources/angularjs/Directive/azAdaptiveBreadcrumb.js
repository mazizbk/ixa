/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 11:52:21
 *
 ******************************************************************************
 *
 * To be used throught azBreadcrumb directive
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azAdaptiveBreadcrumb', [
'$log', '$timeout','$rootScope',
function($log, $timeout, $rootScope) {

    $log = $log.getInstance('azAdaptiveBreadcrumb');

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {

            function adaptBreadcrumb() {
                var model = ngModel.$modelValue;
                scope.hideParentIndicator = false;

                if(undefined == model) {
                    return;
                }

                for(var i=1; i<model.elements.length-1; i++) {
                    model.elements[i].hide = false;
                }

                model.shrinked = false;

                ngModel.$setViewValue(model);
                //$timeout(adaptBreadcrumbElement, 0);
                adaptBreadcrumbElement();
            }

            function adaptBreadcrumbElement() {
                var model = ngModel.$modelValue;

                //  allow overflow to be able to calculate it
                var orginalCssOverflow = element.css('overflow');
                element.css('overflow', 'visible');
                var orginalCssHeight = element.css('height');
                element.css('height', 'auto');

                // detect if content has two lines
                var baseLineHeight = element.css('line-height').replace('px', '');

                var breadcrumbContentHeight = element.height();

                // if content overflows
                if(baseLineHeight*2 <= breadcrumbContentHeight) {
                    // find first visible item
                    var breadcrumbElement = null;
                    // end to length-1 because we never hide the current (last) element in breadcrumb
                    for(var i=0; i<model.elements.length-1; i++) {
                        if(true != model.elements[i].hide) {
                            breadcrumbElement = model.elements[i];
                            break;
                        }
                    }

                    // if we found an element, we hide it and redo the test after dom update
                    if(null != breadcrumbElement) {
                        breadcrumbElement.hide = true;
                        model.shrinked = true;
                        ngModel.$setViewValue(model);
                        $timeout(adaptBreadcrumbElement, 0);
                    }
                    else {
                        // overflow but no other element to hide, hide the parent indicator (the "...")
                        scope.hideParentIndicator = true;
                    }

                }

                element.css('overflow', orginalCssOverflow);
                element.css('height', orginalCssHeight);

            }

            var lastTimer;

            // call function after dom loaded
            // works even with 0 delay because the timeout itself is pushed inside the browser event queue and page renderer is in the queue too
            lastTimer = $timeout(adaptBreadcrumb, 0);

            window.addEventListener('resize',function(){
                $timeout.cancel(lastTimer);
                lastTimer = $timeout(adaptBreadcrumb, 0);
            });

            // listen to custom event interfacePanelResize
            $rootScope.$on('interfacePanelResize',function(){
                $timeout.cancel(lastTimer);
                $timeout(adaptBreadcrumb, 0);
            });

        }
    }
}]);
