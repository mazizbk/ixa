/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:31:09
 *
 ******************************************************************************
 *
 * This directive is to be used with symfony2 Collection Form, it will build DOM elements
 * for Collection items using data-prototype provided as html attribute by symfony2
 * based on the model passed as directive argument
 * ex : azFormDataPrototype="formdata.media['mediaDeclinations']" will loop over each
 * child of 'mediaDeclinations', build a DOM element and the binding will be done
 * with the same naming convention as for other form fields ( formdata.xxx['xxx']... )
 *
 * An optionnal attribute prototype-orderby can be set to activate collection
 * ordering. The value of this attribute define the name of the item's property used to
 * sort the collection (ex: displayOrder)
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azFormDataPrototype', [
'$log', '$compile', '$parse',
function($log, $compile, $parse) {
    $log = $log.getInstance('azFormDataPrototype');

    return {
        restrict: 'A',
        scope: true,
        link: function($scope, element, attrs) {
            var formDataPrototypeModels = [];
            var childrenElements = [];

            var allowAdd = false;
            var allowDelete = false;
            var allowSort = false;
            if (undefined != attrs['prototypeAllowAdd']) allowAdd = true;
            if (undefined != attrs['prototypeAllowDelete']) allowDelete = true;
            if (undefined != attrs['prototypeOrderby']) allowSort = true;

            // get the prototype name wildcard
            var prototypeNameWildcard = attrs['prototypeName'];
            if (undefined == prototypeNameWildcard) prototypeNameWildcard = '__name__';

            // compound prototype means subdata is an object, otherwise it is a simple value
            var prototypeIsCompound = ('true' == attrs['prototypeIsCompound']);

            if (allowAdd) {
                var formAddElement = '<a href class="form-add-btn"><span class="glyphicon glyphicon-plus-sign"></span> ' + Translator.trans('add') + '</a>';
                var formAddDomElement = angular.element(formAddElement);
                element.parent().append(formAddDomElement);
            }

            // This function add or removes DOM elements so there is the same
            // number of DOM elements as objects in the model.
            // It will always removes elements at the end (it won't take care
            // of existing data)
            /*var updateDomElements = function(formDataPrototypeModels) {

                var childrenElements = element.children();

                var elementCount = childrenElements.length;
                var modelCount = formDataPrototypeModels.length;


                // create missing elements from model
                if (modelCount > elementCount) {
                    for (var i=elementCount;i<modelCount;i++) {
                        createDomElement(i, formDataPrototypeModels);
                    }
                }

                // remove elements not in model
                // CAUTION: THIS REMOVE ALWAYS THE LAST ONE
                if (modelCount < elementCount) {
                    for (var i=modelCount;i<elementCount;i++) {
                        childrenElements[i].remove();
                    }
                }

            };*/

            if (allowSort) {
                var orderPropertyName = attrs['prototypeOrderby'];

                var updateChildrenDomElementsOrder = function() {
                    // Sort elements by orderby property
                    var sortedElements = [];
                    for (var i = childrenElements.length - 1; i >= 0; i--) {
                        if (formDataPrototypeModels[i]) {
                            sortedElements[formDataPrototypeModels[i][orderPropertyName]] = childrenElements[i];
                        }
                    }
                    // Update order in DOM
                    for (var i = sortedElements.length - 1; i >= 0; i--) {
                        element.prepend(sortedElements[i]);
                    }
                }
            }

            // retieve the given section in current form bag (with the same field as in azFormDataProtoype attr)
            // and delete the given elementIndex
            var removeElementInFormsBagSection = function(sectionName, elementIndex) {
                var sectionModelName = attrs.azFormDataPrototype.replace('forms.data.', 'forms.' + sectionName + '.');
                var modelSectionParser = $parse(sectionModelName);
                var model = modelSectionParser($scope);
                if (undefined != model) model[elementIndex] = undefined;
            }

            var createDomElement = function(formElementNumber) {
                // get the template (attribute data-prototype)
                var formElement = attrs['prototype'];

                // set the fields names
                formElement = formElement.replace(new RegExp(prototypeNameWildcard + 'label__', 'g'), formElementNumber);
                formElement = formElement.replace(new RegExp(prototypeNameWildcard, 'g'), formElementNumber);

                //attach DOM element ($compile set the bindings)
                var elementScope = $scope.$new(); // Create a new subscope for the element
                var formDomElement = angular.element($compile(formElement)(elementScope));

                if (true == allowDelete) {
                    var formDomElementFields = formDomElement.find('.form-control');

                    //count fields in subform
                    var nbFields = formDomElementFields.length;

                    var formDeleteDomElement = null;

                    if (nbFields == 1) {
                        //check if a input-group div exists
                        var formDomElementFieldsInputGroup = formDomElementFields.parent();
                        if (!formDomElementFieldsInputGroup.hasClass('input-group')) {
                            //create it if doesn't
                            formDomElementFieldsInputGroup = formDomElementFields.wrap('<div class="input-group"></div').parent();
                        }

                        formDeleteDomElement = angular.element('<span class="input-group-addon"><span class="glyphicon glyphicon-trash"></span></span>');
                        formDomElementFieldsInputGroup.append(formDeleteDomElement);

                    }
                    else {
                        formDeleteDomElement = angular.element('<a href class="form-remove-btn"><span class="glyphicon glyphicon-trash"></span> '+Translator.trans('remove')+'</a>');
                        formDomElement.append(formDeleteDomElement);
                    }

                    formDeleteDomElement.bind('click',function() {
                        elementScope.$apply(function() {
                            // remove DOM element
                            formDomElement.remove();
                            // And remove its scope
                            elementScope.$destroy();

                            if (allowSort) {
                                var deletedOrder = formDataPrototypeModels[formElementNumber][orderPropertyName];
                                // Shift other elements order
                                for (var i = formDataPrototypeModels.length - 1; i >= 0; i--) {
                                    if ( deletedOrder < formDataPrototypeModels[i][orderPropertyName]) {
                                        formDataPrototypeModels[i][orderPropertyName]--;
                                    }
                                }
                            }

                            formDataPrototypeModels[formElementNumber] = undefined; // set to undefined instead of splicing the element, because doctrine needs the original indexes to check for existing objects in collection
                            $parse(attrs.azFormDataPrototype).assign($scope, formDataPrototypeModels);

                            removeElementInFormsBagSection('params', formElementNumber);
                            removeElementInFormsBagSection('widgets', formElementNumber);
                            removeElementInFormsBagSection('infos', formElementNumber);
                            removeElementInFormsBagSection('errors', formElementNumber);
                            removeElementInFormsBagSection('values', formElementNumber);
                            removeElementInFormsBagSection('files', formElementNumber);
                        });

                        return false;
                    });
                }

                if (allowSort) {
                    var formDomElementFields = formDomElement.find('.form-control');

                    //count fields in subform
                    var nbFields = formDomElementFields.length;

                    var formSortUpDomElement = null;
                    var formSortDownDomElement = null;
                    var formSortLastUpDomElement = null;
                    var formSortLastDownDomElement = null;

                    if (nbFields == 1) {
                        //check if a input-group div exists
                        var formDomElementFieldsInputGroup = formDomElementFields.parent();
                        if (!formDomElementFieldsInputGroup.hasClass('input-group')) {
                            //create it if doesn't
                            formDomElementFieldsInputGroup = formDomElementFields.wrap('<div class="input-group"></div').parent();
                        }

                        formSortUpDomElement = angular.element('<span class="input-group-addon"><span class="glyphicon glyphicon-chevron-up"></span></span>');
                        formSortDownDomElement = angular.element('<span class="input-group-addon"><span class="glyphicon glyphicon-chevron-down"></span></span>');
                        formSortLastUpDomElement = angular.element('<span class="input-group-addon"><span class="glyphicon glyphicon-pro glyphicon-pro-chevron-last-up"></span></span>');
                        formSortLastDownDomElement = angular.element('<span class="input-group-addon"><span class="glyphicon glyphicon-pro glyphicon-pro-chevron-last-down"></span></span>');

                        formDomElementFieldsInputGroup.append(formSortLastUpDomElement);
                        formDomElementFieldsInputGroup.append(formSortUpDomElement);
                        formDomElementFieldsInputGroup.append(formSortDownDomElement);
                        formDomElementFieldsInputGroup.append(formSortLastDownDomElement);
                    }
                    else {
                        var formSortWrapperDomElement = angular.element('<div class="form-sort-btns"></div>');
                        formSortUpDomElement = angular.element('<a href><span class="glyphicon glyphicon-chevron-up"></span></a>');
                        formSortDownDomElement = angular.element('<a href><span class="glyphicon glyphicon-chevron-down"></span></a>');
                        formSortLastUpDomElement = angular.element('<a href><span class="glyphicon glyphicon-pro glyphicon-pro-chevron-last-up"></span></a>');
                        formSortLastDownDomElement = angular.element('<a href><span class="glyphicon glyphicon-pro glyphicon-pro-chevron-last-down"></span></a>');

                        formSortWrapperDomElement.append(formSortLastUpDomElement);
                        formSortWrapperDomElement.append(formSortUpDomElement);
                        formSortWrapperDomElement.append(formSortDownDomElement);
                        formSortWrapperDomElement.append(formSortLastDownDomElement);

                        formDomElement.append(formSortWrapperDomElement);
                    }

                    var updateModelOrder = function(model, newOrderValue) {
                        var formElementCount  = element.children().length;

                        // Check boundaries
                        if (newOrderValue < 0 || newOrderValue >= formElementCount) {
                            return;
                        }

                        var currentOrderValue = model[orderPropertyName];
                        if (null == currentOrderValue) {
                            currentOrderValue = 0;
                        }

                        var moveSteps = newOrderValue - currentOrderValue;

                        var minIndexUpdate;
                        var maxIndexUpdate;
                        if (moveSteps > 0) {
                            minIndexUpdate = currentOrderValue;
                            maxIndexUpdate = newOrderValue;
                        }
                        else {
                            minIndexUpdate = newOrderValue - 1;
                            maxIndexUpdate = currentOrderValue - 1;
                        }

                        // Shift other elements order
                        for (var i = formDataPrototypeModels.length - 1; i >= 0; i--) {
                            if (formDataPrototypeModels[i] && formDataPrototypeModels[i][orderPropertyName] > minIndexUpdate && formDataPrototypeModels[i][orderPropertyName] <= maxIndexUpdate) {
                                formDataPrototypeModels[i][orderPropertyName] -= Math.sign(moveSteps);
                            }
                        }

                        // Update element order
                        model[orderPropertyName] = newOrderValue;

                        updateChildrenDomElementsOrder();
                    };

                    formSortUpDomElement.bind('click',function() {
                        $scope.$apply(function() {
                            updateModelOrder(formDataPrototypeModels[formElementNumber], formDataPrototypeModels[formElementNumber][orderPropertyName] - 1);
                        });

                        return false;
                    });

                    formSortDownDomElement.bind('click',function() {
                        $scope.$apply(function() {
                            updateModelOrder(formDataPrototypeModels[formElementNumber], formDataPrototypeModels[formElementNumber][orderPropertyName] + 1);
                        });

                        return false;
                    });

                    formSortLastUpDomElement.bind('click',function() {
                        $scope.$apply(function() {
                            updateModelOrder(formDataPrototypeModels[formElementNumber], 0);
                        });

                        return false;
                    });

                    formSortLastDownDomElement.bind('click',function() {
                        $scope.$apply(function() {
                            updateModelOrder(formDataPrototypeModels[formElementNumber], formDataPrototypeModels.length-1);
                        });

                        return false;
                    });

                    // Plug drag'n drop

                    // Expose model on scope so we can use it in drag'n drop directives
                    $scope.formDataPrototypeModels = formDataPrototypeModels;

                    // Add a wrapper and bind drag'n drop directive on it
                    var formWrapperElement = '<div drag="formDataPrototypeModels[' + formElementNumber + ']" drag-type="form-prototype-sort" drag-style="form-prototype-sort-draging" drop="formDataPrototypeModels[' + formElementNumber + ']" drop-type="form-prototype-sort" drop-style="form-prototype-sort-droping"></div>';
                    var formWrapperDomElement = angular.element($compile(formWrapperElement)($scope));
                    formWrapperDomElement.append(formDomElement);
                    formDomElement = formWrapperDomElement;

                    $scope.$on('dropEvent', function(evt, dragged, droppedOn) {
                        if (droppedOn != formDataPrototypeModels[formElementNumber]) {
                            return;
                        }
                        if (!dragged || dragged == droppedOn) {
                            return;
                        }

                        $scope.$apply(function() {
                            updateModelOrder(dragged, droppedOn[orderPropertyName]);
                        });
                    });
                }

                if (allowDelete) {
                    formDomElement.prepend(angular.element('<div class="clearfix"></div>'));
                }

                element.append(formDomElement);

                // Add element in index
                childrenElements[formElementNumber] = formDomElement;
            };

            //get the model passed in params
            $scope.$watch(attrs['azFormDataPrototype'], function(newVal, oldVal) {
                //section of the model we will bind
                formDataPrototypeModels = newVal;

                if (allowSort) {
                    // Reindex model by order property
                    // (caution: we do not alter original model because doctrine need to keep the same indexes in collection)
                    var orderIndexedFormDataPrototypeModels = [];
                    for (var i in formDataPrototypeModels) {
                        var orderValue = formDataPrototypeModels[i][orderPropertyName];
                        if (null == orderValue) {
                            orderValue = i;
                        }
                        orderIndexedFormDataPrototypeModels[orderValue] = formDataPrototypeModels[i];
                    }

                    // Check and repair sequence order

                    // Eliminate null element in order sequence
                    orderIndexedFormDataPrototypeModels = orderIndexedFormDataPrototypeModels.filter(function(element) {
                        return element != null;
                    });
                    // Reapply order property
                    for (var i = orderIndexedFormDataPrototypeModels.length - 1; i >= 0; i--) {
                        if (i != orderIndexedFormDataPrototypeModels[i][orderPropertyName]) {
                            orderIndexedFormDataPrototypeModels[i][orderPropertyName] = i;
                        }
                    }
                }

                // remove all DOM elements
                element.empty();
                //updateDomElements(formDataPrototypeModels);

                if (undefined != formDataPrototypeModels) {
                    // formDataPrototypeModels can be array or object (associative array)
                    angular.forEach(formDataPrototypeModels, function(value, key) {
                        createDomElement(key);
                    });
                }
            });

            //set clic binding on "add new" dom element
            if (allowAdd) {
                formAddDomElement.bind('click', function(evt) {
                    evt.preventDefault();

                    var formElementCount  = element.children().length;

                    // NB: we don't alter or reuse collection indexes, because Doctrine need them unchanged
                    var newElementIndex = formDataPrototypeModels ? formDataPrototypeModels.length : 0;

                    // Handle associative array (= object)
                    if (formDataPrototypeModels && !angular.isArray(formDataPrototypeModels)) {
                        angular.forEach(formDataPrototypeModels, function(value, key) {
                            newElementIndex = key;
                        });
                        newElementIndex = newElementIndex + '_1';
                    }

                    $scope.$apply(function() {

                        // initialise collection model if undefined
                        if (undefined == formDataPrototypeModels) {
                            formDataPrototypeModels = [];

                            // initialise submodel
                            formDataPrototypeModels[newElementIndex] = prototypeIsCompound ? {} : '';

                            $parse(attrs.azFormDataPrototype).assign($scope, formDataPrototypeModels);
                        }
                        else {
                            // initialise submodel object
                            formDataPrototypeModels[newElementIndex] = prototypeIsCompound ? {} : '';
                        }

                        if (allowSort) {
                            formDataPrototypeModels[newElementIndex][orderPropertyName] = formElementCount;
                        }

                        createDomElement(newElementIndex);
                    });
                });
            }
        }
    };
}]);
