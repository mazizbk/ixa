/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-19 14:12:28
 *
 ******************************************************************************
 *
 * This directive is made for binding a list of object to multiple checkboxes
 *
 * for example, having in model :
 *     groups : [
 *          {id: '1'},
 *          {id: '3'}
 *       ]
 *
 * and binding to checkboxes :
 *     <input type="checkbox" ng-model="forms.data.groups" id="groups_1" name="groups[]" value="1" />
 *     <input type="checkbox" ng-model="forms.data.groups" id="groups_2" name="groups[]" value="2" />
 *     <input type="checkbox" ng-model="forms.data.groups" id="groups_3" name="groups[]" value="3" />
 *
 * then checkboxes 1 and 3 should be checked
 *
 * Usage :
 *     <input type="checkbox" id="user_groups_1" name="user[groups][]" az-compound-checkboxes="forms.data.user['groups']" az-compound-checkboxes-values="forms.values.user['groups']" value="1" />
 *
 *     Value is the id of the object model
 *     If value is a variable, you can use ng-value
 *
 *     Optionnal inherited data representation :
 *         This directive allow a third state representation of the data, for displaying inheritance (for example a user inheriting a right from his group)
 *         This is readonly and not binded to the form model
 *             <input type="checkbox" ... az-compound-checkboxes="forms.data.user['myAccessRight']" az-compound-checkboxes-values="forms.values.user['myAccessRight']" value="1" az-compound-checkboxes-inherited="forms.inheritedData.user['myAccessRight']" />
 *
 * Usage without az-compound-checkboxes-values:
 *     if object behind az-compound-checkboxes-values is null, the value attribute from inputs will be litteraly use as data for the model.
 *
 *     <input type="checkbox" name="something[categories][]" az-compound-checkboxes="forms.data.something['categories']" value="My category A" />
 *     <input type="checkbox" name="something[categories][]" az-compound-checkboxes="forms.data.something['categories']" value="My category B" />
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azCompoundCheckboxes', [
'$log', '$compile', '$parse', 'ArrayExtra',
function($log, $compile, $parse, ArrayExtra) {

    $log = $log.getInstance('azCompoundCheckboxes');

    // opacity of the third state (inherited data)
    var inheritedStateOpacity = 0.5;

    function findParentObjectExpression(expression) {
        var pos = expression.lastIndexOf('[');
        if (-1 == pos) return undefined;
        return expression.substring(0,pos);
    }

    function link(scope, element, attrs) {
        // compile for binding our new local ng-model ('checked')
        $compile(element)(scope);

        // shadow element is the DOM element containing the inherited value (third-state)
        var shadowElement;

        var modelParser = $parse(attrs.azCompoundCheckboxes);
        var elementValue = element.val();

        if (undefined != attrs.ngValue) {
            elementValue = scope.$eval(attrs.ngValue);
        }

        var modelData;
        var modelDataPossibleValues = scope.$eval(attrs.azCompoundCheckboxesValues);
        var checkboxData = null;

        if (!angular.isArray(modelDataPossibleValues)) {
            // if no model set for possible value, use index as value
            checkboxData = elementValue;

            modelDataPossibleValues = null;
        }
        else {
            if (modelDataPossibleValues.length == 0) {
                $log.error('No affectable values set for checkbox: ', attrs.azCompoundCheckboxesValues);
                return;
            }

            // retrieve checkbox data model from list
            checkboxData = ArrayExtra.findFirstInArray(modelDataPossibleValues,{id: elementValue});
        }

        if (!checkboxData) {
            $log.error('Could not retrieve data associated to a checkbox of '+ attrs.azCompoundCheckboxes);
            return;
        }

        // uncheck the box by default
        scope.checked = false;

        //var stopWatcherPropagation = false;

        // initial watcher, check if data exists, initialise if not
        scope.$watchCollection(attrs.azCompoundCheckboxes, function(newValue, oldValue) {
            //stopWatcherPropagation = true;
            modelData = newValue;

            if (undefined == modelData) {
                scope.checked = false;

                // find the parent object in object expression
                var parentObjectExpression = findParentObjectExpression(attrs.azCompoundCheckboxes);

                // if the model data is not defined and its parent is defined, initialise the array
                if (undefined != parentObjectExpression && undefined != scope.$eval(parentObjectExpression)) {
                    modelParser.assign(scope, []);
                }
            }
            else {
                var existingDataObject = null;

                if (null == modelDataPossibleValues) {
                    // if there is only one element in the array, the serializer can return an object instead of an array
                    // ex: entity.categories = {1: 'mycat1'} instead of entity.categories = ['mycat1']
                    // so we fix model data
                    if (!angular.isArray(modelData)) {
                        modelData = [modelData[1]];
                        modelParser.assign(scope, modelData);
                    }

                    for (var i = modelData.length - 1; i >= 0; i--) {
                        // if model item is object, replace object by its id
                        if (angular.isObject(modelData[i])) {
                            modelData[i] = ''+modelData[i].id;
                        }
                    }

                    if (angular.isObject(elementValue)) {
                        existingDataObject = ArrayExtra.findFirstInArray(modelData, elementValue);
                    }
                    else if (-1 != modelData.indexOf(elementValue)) {
                        existingDataObject = elementValue;
                    }
                }
                else {
                    // initialise checkbox state from model data
                    // check if an element with the corresponding id is in the original model
                    // (check if model is the real object from possible values list, or if is just a plain
                    // object containing the reference the real one)
                    existingDataObject = ArrayExtra.findFirstInArray(modelData, {id: elementValue});
                }

                if (existingDataObject) {
                    if (existingDataObject != checkboxData) {
                        // replace the element with the corresponding one from checkbox possible values list
                        modelParser(scope).splice(modelData.indexOf(existingDataObject), 1, checkboxData); // this re-trigger the watcher a second time
                    }
                    scope.checked = true;
                    if (shadowElement) element.css('opacity', 1);
                }
                else {
                    scope.checked = false;
                    if (shadowElement) element.css('opacity', inheritedStateOpacity);
                }
            }
        });

        scope.$watch('checked', function(newValue, oldValue) {
            /*if (stopWatcherPropagation) {
                $log.debug('checked model watcher propagation stopped');
                stopWatcherPropagation = false;
                return;
            }*/

            // if model data not yet set, do nothing
            if (!modelData) return;

            // the box has been checked
            if (newValue) {
                // check if the element is not already in the model data
                if (modelData.indexOf(checkboxData) == -1) {
                    // add the checkbox's object to the model list
                    modelParser(scope).push(checkboxData);
                }

                if (shadowElement) element.css('opacity', 1);
            }

            // the box has been unchecked
            else {
                var checkboxDataIndex = modelData.indexOf(checkboxData);

                // check if the element is already in the model data
                if (checkboxDataIndex > -1) {
                    // remove the checkbox's object to the model list
                    modelParser(scope).splice(checkboxDataIndex, 1);
                }

                if (shadowElement) element.css('opacity', inheritedStateOpacity);

            }
        });

        // handle checkbox third state (inherited data visualisation)

        var inheritedModelParser = $parse(attrs.azCompoundCheckboxesInherited);

        scope.$watchCollection(attrs.azCompoundCheckboxesInherited, function(newValue, oldValue) {
            if (!angular.isArray(newValue)) return;

            // insert shadow checkbox if not exists
            if (undefined == shadowElement) {

                var ciblingElement = element;

                if ('label' == element.parent()[0].tagName.toLowerCase()) {
                    ciblingElement = element.parent();
                }

                shadowElement = $('<input type="checkbox" readonly="readonly" />').insertBefore(ciblingElement);
            }

            // find object in collection, corresponding to this checkbox
            var existingDataObject = ArrayExtra.findFirstInArray(newValue, {id: elementValue});

            // if not a real object in the possible value list, find it from its id
            if (existingDataObject && existingDataObject != checkboxData) {
                // replace the element with the corresponding one from checkbox possible values list
                inheritedModelParser(scope).splice(modelData.indexOf(existingDataObject), 1, checkboxData); // this re-trigger the watcher a second time
            }

            element.css('opacity', (scope.checked)? 1 : inheritedStateOpacity);

            // map model to checkbox
            shadowElement[0].checked = existingDataObject ? true : false;

        });
    }

    return {
        restrict: 'A',
        scope: true, // isolate scope
        compile: function(element, attrs) {
            // add a model attached to the scope to represent the state of the checkbox
            element.attr('ng-model', 'checked');

            // store target model name before removing the attr
            element.attr('az-compound-checkboxes-model', element.attr('az-compound-checkboxes'));

            // prevent from compile recursions
            element.removeAttr('az-compound-checkboxes');

            // TODO: check validity of params

            // redirect to link function
            return link;
        }
    }
}]);
