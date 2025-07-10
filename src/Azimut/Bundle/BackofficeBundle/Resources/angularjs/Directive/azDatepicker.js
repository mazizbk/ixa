/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-12 14:46:01
 *
 ******************************************************************************
 *
 *  Plug Air date or datetime picker
 *
 *  Usage :
 *      Date picker : <input type="text" az-datepicker ng-model="forms.data.myObject['myDateProperty']" />
 *      Date picker : <input type="text" az-datepicker az-datepicker-time="true" ng-model="forms.data.myObject['myDatetimeProperty']" />
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azDatepicker', [
'$rootScope',
function($rootScope) {
    return {
        restrict: 'A',
        priority: 1,
        require: 'ngModel',
        scope: {
            'model': '=ngModel',
        },
        link: function(scope, element, attrs, formController) {
            var isFirstDataSet = true;
            var hasTimepicker = ('true' == attrs.azDatepickerTime);

            // Not using US format by default, because it is not the most used in the world
            // See https://en.wikipedia.org/wiki/Date_format_by_country
            var momentDateFormat = 'DD/MM/YYYY';
            var momentTimeFormat = 'HH:mm';
            var pickerDateFormat = 'dd/mm/yyyy';
            var pickerTimeFormat = 'hh:ii';
            if ('en' == $rootScope.locale) {
                // var momentDateFormat = 'MM/DD/YYYY'; // US format, but not GB, as we don't split english locales, use the default one
                var momentTimeFormat = 'hh:mm a';
                // var pickerDateFormat = 'mm/dd/yyyy'; // US format, but not GB, as we don't split english locales, use the default one
                var pickerTimeFormat = 'hh:ii aa';
            }
            var momentFormat = hasTimepicker ? momentDateFormat + ' ' + momentTimeFormat : momentDateFormat;

            var date = null;

            var datepicker = element.datepicker({
                language: $rootScope.locale,
                dateFormat: pickerDateFormat,
                timeFormat: pickerTimeFormat,
                timepicker: hasTimepicker,
                onSelect: function() {
                    element.trigger('change');
                }
            }).data('datepicker');

            // Watch external date changes (Note that APIs do not returns view formated date like 2019-11-08T17:51:30+01:00)
            scope.$watch('model', function(model) {
                var newDate;

                if (undefined == model) {
                    return;
                }

                // Detect date format (just by searching slash, may improve this)
                if (model.indexOf('/') == -1 ) {
                    // Full date from APIs
                    newDate = moment(model);
                }
                else {
                    // Already formated date
                    newDate = moment(model, momentFormat);
                }

                if (false == newDate.isValid()) {
                    console.error('Tried to affet invalid date to "' + attrs.ngModel + '", ignoring');
                    return;
                }

                // Ignore if model underlying value has not changed
                if (null != date && newDate.toString() == date.toString()) {
                    return;
                }

                date = newDate;
                scope.model = date.format(momentFormat);
                datepicker.selectDate(date.toDate());

                if (isFirstDataSet) {
                    // remove dirty state on form
                    formController.$setPristine();
                }

                isFirstDataSet = false;
            });
        }
    }
}]);
