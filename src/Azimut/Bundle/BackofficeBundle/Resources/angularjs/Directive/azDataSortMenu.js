/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-28 10:58:57
 ******************************************************************************
 *
 * Display a dropdown menu of links for a DataSortDefinitionBuilder object
 *
 * Usage :
 *     <az-data-sort-menu ng-model="filesSortDefinitionBuilder"></az-data-sort-menu>
 */

'use strict';

angular.module('azimutBackoffice.directive')
.directive('azDataSortMenu', [
'$log', 'DataSortDefinitionBuilder',
function($log, DataSortDefinitionBuilder) {
    $log = $log.getInstance('azDataSortMenu');

    return {
        restrict: 'E',
        transclude: true,
        require: 'ngModel',
        scope: {
            'model': '=ngModel',
        },
        link: function(scope, element, attrs, ngModel)  {
            scope.$watch('model', function(model) {
                if (undefined == model) {
                    return;
                }

               if (!(model instanceof DataSortDefinitionBuilder)) {
                   $log.error('ngModel parameter must be an instance of DataSortDefinitionBuilder');
                   return;
               }
               scope.dataSortDefinitionBuilder = model;
           });
        },
        template:
            '<span class="dropdown dropup listingSortDropDown">'+
                '<a href role="button" data-toggle="dropdown"><span class="glyphicon glyphicon-sort-by-attributes-alt"></span> '+ Translator.trans('sort.by') + ' : {{ dataSortDefinitionBuilder.label }} <span class="caret"></span></a>'+
                '<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="listingSortDropDown">'+
                    '<li ng-repeat-start="sortDefinition in dataSortDefinitionBuilder.definitions | orderBy: \'label\'"><a href ng-click="dataSortDefinitionBuilder.sortBy(sortDefinition.property, false)">{{ sortDefinition.label | capitalize }}</a></li>'+
                    '<li ng-repeat-end><a href ng-click="dataSortDefinitionBuilder.sortBy(sortDefinition.property, true)">{{ sortDefinition.label|capitalize }} - ' + Translator.trans('desc') +'</a></li>'+
                '</ul>'+
            '</span>'
    }
}]);
