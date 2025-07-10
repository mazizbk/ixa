/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-05-27 14:46:14
 *
 ******************************************************************************
 *
 * Mediacenter state provider : responsible for attaching all states to a parent
 * (either the app itself, or another state for widget mode)
 *
 */

'use strict';
angular.module('azimutMediacenter.service')

.provider('MediacenterState', [
'MediacenterStateDefinition', '$stateProvider',
function (MediacenterStateDefinition, $stateProvider) {
    var attachStateTo = function(stateDefinition, parentStateName) {
        var state = angular.copy(stateDefinition);
        state.name = parentStateName + '.' + state.name;

        $stateProvider.state(state);
    };

    // Directly attach states to a parent state
    this.attachNativeStatesTo = function(parentStateName) {
        angular.forEach(MediacenterStateDefinition, function(stateDefinition) {
            attachStateTo(stateDefinition, parentStateName);
        });
    };

    // Attach widget states to a named subviews (widget mode)
    this.attachStatesTo = function(parentStateName) {
        angular.forEach(MediacenterStateDefinition, function(stateDefinition) {
            // Attach the main state on the widget views (all others are main's subviews)
            if ('mediacenter' == stateDefinition.name) {
                var state = angular.copy(stateDefinition);
                state.name = parentStateName + '.' + state.name;
                state.views = {
                    'mediacenter-widget': {
                        templateUrl: state.templateUrl,
                        controller: state.controller
                    }
                };

                delete state.templateUrl;
                delete state.controller;

                $stateProvider.state(state);
            }
            else {
                attachStateTo(stateDefinition, parentStateName);
            }
        });
    };

    // Factory service returned after config phase (not used in our case)
    var MediacenterStateFactory = function() {
    };

    this.$get = function() {
        return MediacenterStateProvider;
    };
}]);
