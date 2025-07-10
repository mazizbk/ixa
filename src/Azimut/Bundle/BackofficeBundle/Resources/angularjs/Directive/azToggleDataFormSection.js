/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-28 16:11:37
 *
 ******************************************************************************
 *
 * Toggle specific section of form based on the value of an input
 * (within the same id's prefix)
 *
 * Usage :
 *
 * The Toggler:
 *     <select ng-model="forms.data.my_form['my_field']" id="my_form_my_field" az-toggle-data-form-section="my_section_name">
 *         <option value="lorem">Lorem</option>
 *         <option value="ipsum">Ipsum</option>
 *     </select>
 *
 * Sections shown only when toggler value = "lorem" (it can be many of them):
 *     <div data-form-section="my_form_my_field---my_section_name" data-form-section-value="lorem">My lorem section 1</div>
 *     <div data-form-section="my_form_my_field---my_section_name" data-form-section-value="lorem">My lorem section 2</div>
 *
 * Section shown only when toggler value = "ipsum":
 *     <div data-form-section="my_form_my_field---my_section_name" data-form-section-value="ipsum">My ipsum section 1</div>
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azToggleDataFormSection', [
'$log',
function($log) {
    $log = $log.getInstance('azToggleDataFormSection');
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var parentId = attrs.id.substring(0, attrs.id.lastIndexOf('_'));
            var dataFormSection = parentId + '---' + attrs.azToggleDataFormSection;

            scope.$watch(attrs.ngModel, function(newValue) {
                // hide elements of the same section
                $('[data-form-section="'+ dataFormSection + '"]').hide();

                // show only elements of the same section and the same value
                $('[data-form-section="'+ dataFormSection + '"][data-form-section-value="' + newValue + '"]').show();
            });
        }
    }
}]);
