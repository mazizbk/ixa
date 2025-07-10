/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:29:52
 *
 ******************************************************************************
 *
 * This directive comes with EntityJsFormType.
 * It drives an hidden field that will be filled with an entity id returned by a provided javascript function.
 * It can be used to build a personalized widget for choosing an entity.
 *
 * Parameters for the widget will be set on $scope.appScope
 * For proper namespacing in widgets, $scope.appScope has to be set on the main scope of your application (ex: $scope.appScope = $scope)
 *
 * attributes :
 *    - azFormEntityJs: id of the widget
 *    - azFormEntityJsParams: widgetParams object, containing :
 *          - containerId: the id of the dom element containing the widget that has to be show
 *          - modalId: the id of the modal containing the widget that has to be show
 *          - callbackName: the name of the callback inside the widget on wich it will be plugged
 *          - onShow(void): function called when the widget is shown
 *          - onSet(value): function called when the widget set the value
 *          - buttonLabel: label of the widget's button
 *          - hideLabel: hide the label of the widget (input field)
 *          - params: object containing params for widget (can hold different properties like statePrefix, acceptedTypes, etc.)
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azFormEntityJs', [
'NestedModelInstanciator', '$parse', '$log',
function(NestedModelInstanciator, $parse, $log) {

    $log = $log.getInstance('azFormEntityJs');

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function($scope, element, attrs, ngModel) {

            // TODO: refactor this with azTinymce

            var widgetId = attrs['azFormEntityJs'];
            var widgetParamsObjName = attrs['azFormEntityJsParams'];
            var widgetCompoundParamsObjName = attrs['azFormEntityJsCompoundParams'];

            if (null != widgetId && null != widgetParamsObjName) {
                var widgetButton = element.find('#'+widgetId+'-button');
                var widgetLabel = element.find('#'+widgetId+'-label');
                var widgetImg = element.find('#'+widgetId+'-img');
                var widgetRemoveButton = element.find('#'+widgetId+'-remove-button');

                var plugWidget = function(widgetParams) {
                    NestedModelInstanciator.instanciate(attrs.ngModel, $scope);

                    var widgetModal = null;

                    //if a modal has to be shown, first retrieve the modal dom object
                    if (null != widgetParams.modalId) {
                        widgetModal = $('#'+widgetParams.modalId);
                    }

                    //set namespace for widget params
                    if (undefined == $scope.appScope) {
                        $scope.appScope = $scope;
                        $log.warn('$scope.appScope was not set, it has been initialised but may not work as expected. See azFormEntityJs documentation.');
                    }

                    if (undefined == $scope.appScope.azimutWidgetsParams) $scope.appScope.azimutWidgetsParams = {};
                    if (undefined == $scope.appScope.azimutWidgetsParams[widgetId]) $scope.appScope.azimutWidgetsParams[widgetId] = {};

                    //register params for widget
                    $scope.appScope.azimutWidgetsParams[widgetId].params = widgetParams.params;

                    // plug callback function
                    if (undefined == $scope.appScope.azimutWidgetsParams[widgetId].callbacks) $scope.appScope.azimutWidgetsParams[widgetId].callbacks = [];
                    $scope.appScope.azimutWidgetsParams[widgetId].callbacks[widgetParams.callbackName] = function(results,options) {
                        // set model value
                        ngModel.$setViewValue(results[0].id);
                        widgetLabel.addClass('ng-dirty');

                        // set name info model value
                        $parse(widgetLabel.attr('ng-value')).assign($scope, results[0].name);

                        // set img info model value
                        $parse(widgetImg.attr('ng-model')).assign($scope, results[0].thumb);

                        //close modal
                        if (widgetModal) {
                            widgetModal.modal('hide');
                        }

                        //if a DOM container has been specified, hide it
                        if (null != widgetParams.containerId) {
                            $('#'+widgetParams.containerId).hide();
                        }

                        if (null != widgetParams.onSet) widgetParams.onSet(results[0].id);

                        //TODO : handle multiple results

                    };

                    widgetButton.bind('click', function() {
                        //set the widgetId on the application scope so the widget can access it even if not a direct child
                        $scope.$apply(function() {
                            $scope.appScope.widgetId = widgetId;
                        });

                        if (widgetModal) {
                            widgetModal.modal('show');
                        }

                        //if a DOM container has to be shown
                        if (null != widgetParams.containerId) {
                            $('#'+widgetParams.containerId).show();
                        }

                        if (null != widgetParams.onShow) widgetParams.onShow();

                        return false;
                    });

                    widgetRemoveButton.bind('click', function() {
                        $scope.$apply(function() {
                            ngModel.$setViewValue(undefined);
                            // empty thumb name model
                            $parse(widgetLabel.attr('ng-value')).assign($scope, undefined);

                            // empty thumb img model
                            $parse(widgetImg.attr('ng-model')).assign($scope, undefined);
                        });

                        if (null != widgetParams.onSet) widgetParams.onSet(undefined);


                        return false;
                    });

                    $scope.$watch(widgetImg.attr('ng-model'), function(thumb) {
                        if (undefined != thumb) widgetImg.attr('src', Routing.generate('azimut_mediacenter_backoffice_file_proxy_thumb',{ filepath: thumb, size: 'xs' }))
                    });
                }

                //retrieve param object
                $scope.$watch(widgetParamsObjName, function(widgetParams) {
                    if (!widgetParams) {
                        // if config object not found, look for a commun one
                        $scope.$watch(widgetCompoundParamsObjName, function(widgetParams) {
                            if (!widgetParams) return;

                            plugWidget(widgetParams);

                        });

                        return;

                    }

                    plugWidget(widgetParams);
                });
            }

        }
    }
}]);
