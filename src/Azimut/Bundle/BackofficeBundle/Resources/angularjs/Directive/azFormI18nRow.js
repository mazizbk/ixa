/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:37:43
 *
 ******************************************************************************
 *
 * Controls the visibility of a localized form field
 * based on the $scope.formLocale (the locale we want to display)
 * and data-form-i18n attribute (the actual locale of the field)
 * is $scope.formLocale is null, it displays all fields.
 * It also initialize the corresponding object model if needed
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azFormI18nRow', [
'$parse', 'NestedModelInstanciator',
function($parse, NestedModelInstanciator) {
    return {
        link: function($scope, element, attrs) {

            var model = $scope.$eval(attrs.ngModel);

            // transform the array model into object (because we want an associative array and javascript doesn't this like an object)
            if(angular.isArray(model)) {
                var model = $parse(attrs.ngModel);
                model.assign($scope, {});
            }

            //initialize i18n subobject in formdata
            //if the model associated to the field is not instantiated
            NestedModelInstanciator.instanciate(attrs.ngModel,$scope);


            var fieldLocale = attrs.formI18nRow;

            $scope.$watch(attrs.azFormI18nRow, function(formLocale) {
                if(null != formLocale && formLocale != fieldLocale) {
                    //element.css('display','none');
                    element.addClass('hidden');
                }
                else {
                    //element.css('display','block');
                    element.removeClass('hidden');
                }
            });
        }
    };
}]);
