/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-25 16:50:39
 ******************************************************************************
 *
 * Create a link to manage the sort property of a DataSortDefinitionBuilder object
 *
 * Usage :
 *     <th az-data-sort-link="filesSortDefinitionBuilder" az-data-sort-link-property="name">Name</th>
 *
 *     az-data-sort-link (DataSortDefinitionBuilder): an instance of DataSortDefinitionBuilder
 *     az-data-sort-link-property (string) : name of the property for sorting
 */

'use strict';

angular.module('azimutBackoffice.directive')
.directive('azDataSortLink', [
'$log', 'DataSortDefinitionBuilder',
function($log, DataSortDefinitionBuilder) {
    $log = $log.getInstance('azDataSortLink');

    return {
        restrict: 'A',
        transclude: true,
        scope: {
            'azDataSortLink': '=',
        },
        link: function(scope, element, attrs)  {
            if (!(scope.azDataSortLink instanceof DataSortDefinitionBuilder)) {
                $log.error('azDataSortLink parameter must be an instance of DataSortDefinitionBuilder');
            }

            scope.dataSortDefinitionBuilder = scope.azDataSortLink;
            scope.sortProperty = attrs.azDataSortLinkProperty;
            scope.sortReverse = (true == scope.dataSortDefinitionBuilder.findDefinition(scope.sortProperty).reverse);

            element.attr('ng-click', ''); // Add empty ng-click, just to display pointer cursor via css

            element.on('click',function(evt) {
                scope.$apply(function() {
                    var reverse = scope.sortReverse;

                    // Inverse direction if property in use
                    if (scope.dataSortDefinitionBuilder.isSortedBy(scope.sortProperty, scope.sortReverse)) {
                        reverse = !reverse;
                    }

                    scope.dataSortDefinitionBuilder.sortBy(scope.sortProperty, reverse);
                });
            });
        },
        template:
            '<span ng-transclude></span>'+
            '<span class="sort-icon" ng-class="{\'selected\': azDataSortLink.isSortedBy(sortProperty)}">' +
                '<span class="caret" ng-class="{\'caret-inverse\': azDataSortLink.isSortedBy(sortProperty, true)}"></span>' +
            '</span>'
    }
}]);
