/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-25 15:00:22
 ******************************************************************************
 *
 * Handle data sorting definition (for Angular orderBy filter) with local storage
 *
 * Usage
 * -----
 *
 * In controller, instantiate the builder whith sort definitions (don't forget to inject DataSortDefinitionBuilder) :
 *
 * $scope.filesSortDefinitionBuilder = new DataSortDefinitionBuilder('mediacenter-files-' + file.id, [
 *     {
 *         'label': Translator.trans('name'),
 *         'property': 'name',
 *         'default': true      // Is default sort property
 *     },
 *     {
 *         'label': Translator.trans('creation.date'),
 *         'property': 'id',
 *         'reverse': true      // Is inverse sort by default
 *     },
 *     {
 *         'label': Translator.trans('type'),
 *         'property': 'type'
 *     }
 * ]);
 *
 * In view :
 *
 * Plug filter to "property" and "reverse" properties of the builder :
 *
 * <div ng-repeat="file in file.subfolders | orderBy: natural(filesSortDefinitionBuilder.property):filesSortDefinitionBuilder.reverse">
 *
 * To toggle sort :
 *     <a href ng-click="filesSortDefinitionBuilder.sortBy('name')"></a>
 *     (calling sortBy twice with the same property will invert sorting)
 *
 * To set sort on a specific direction, set the reverse argument :
 *     <a href ng-click="filesSortDefinitionBuilder.sortBy('name', true)"></a>
 *
 * Shortcut to display data sort link (using azDataSortLink directive) :
 *     <th az-data-sort-link="filesSortDefinitionBuilder" az-data-sort-link-property="name">Name</th>
 *
 * Build menu with available sortings :
 *     <az-data-sort-menu ng-model="filesSortDefinitionBuilder"></az-data-sort-menu>
 *
 * Build custom menu with available sortings :
 *     <ul>
 *         <li ng-repeat="sortDefinition in filesSortDefinitionBuilder.definitions | orderBy: 'label'">
 *             <a href ng-click="filesSortDefinitionBuilder.sortBy(sortDefinition.property)">{{ sortDefinition.label }}</a>
 *         </li>
 *     </ul>
 *
 * Build custom menu with available sortings and DESC links :
 *     <ul>
 *         <li ng-repeat-start="sortDefinition in filesSortDefinitionBuilder.definitions | orderBy: 'label'">
 *             <a href ng-click="filesSortDefinitionBuilder.sortBy(sortDefinition.property, false )">{{ sortDefinition.label }}</a>
 *         </li>
 *         <li ng-repeat-end>
 *             <a href ng-click="filesSortDefinitionBuilder.sortBy(sortDefinition.property, true )">{{ sortDefinition.label }} - desc</a>
 *         </li>
 *     </ul>
 */

'use strict';
angular.module('azimutBackoffice.service')

.factory('DataSortDefinitionBuilder', [
'$log', 'ArrayExtra',
function($log, ArrayExtra) {
    $log = $log.getInstance('DataSortDefinitionBuilder');

    var DataSortDefinitionBuilder = function(sortId, definitions) {
        this.id = sortId;
        this.property = null;
        this.label = '';
        this.reverse = false;
        this.definitions = definitions;

        // Set default values
        for (var i = 0, length = definitions.length; i < length; i++) {
            if (true == definitions[i].default) {
                this.property = definitions[i].property;
                this.reverse = definitions[i].reverse;
                this.label = definitions[i].label;
            }
        }

        // Restore sorting from localStorage
        var storedProperty = localStorage.getItem(this.getLocalStoragePropertyName());//'azimutMediacenter-order-files-by-'+file.id);
        if (storedProperty) {
            var storedReverse = ('true' == localStorage.getItem(this.getLocalStorageReverseName()) ? true : false);
            this.sortBy(storedProperty, storedReverse);
        }

        return this;
    };

    DataSortDefinitionBuilder.prototype.getLocalStoragePropertyName = function() {
        return 'azimut-sort-definition-' + this.id + '-property';
    }

    DataSortDefinitionBuilder.prototype.getLocalStorageReverseName = function() {
        return 'azimut-sort-definition-' + this.id + '-reverse';
    }

    DataSortDefinitionBuilder.prototype.sortBy = function(property, reverse) {
        // If reverse not provided and property unchanged, invert reverse property
        if (undefined == reverse && property == this.property) {
            reverse = !this.reverse;
        }

        // Find property's definition in definitions
        var sortDefinition = this.findDefinition(property);

        if (null == sortDefinition) {
            // NB: we don't thow error here because we don't want to block the execution
            $log.error('Unable to find sort definition for property "' + property + '"');
            return false;
        }

        this.property = property;
        this.reverse = reverse;
        this.label = sortDefinition.label + (reverse ? ' - ' + Translator.trans('desc') : '');

        // Store informations in local storage
        localStorage.setItem(this.getLocalStoragePropertyName(), property);
        localStorage.setItem(this.getLocalStorageReverseName(), reverse);
    }

    DataSortDefinitionBuilder.prototype.isSortedBy = function(property, reverse) {
        if (undefined == reverse) {
            return property == this.property;
        }

        return property == this.property && reverse == this.reverse;
    }

    // Find property's definition in definitions
    DataSortDefinitionBuilder.prototype.findDefinition = function(property) {
        return ArrayExtra.findFirstInArray(this.definitions, { 'property': property });
    }

    return DataSortDefinitionBuilder;
}]);
