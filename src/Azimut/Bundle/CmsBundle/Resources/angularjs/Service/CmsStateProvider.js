/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-25 12:06:08
 *
 ******************************************************************************
 *
 * Cms state provider : responsible for attaching all states to a parent
 * (either the app itself, or another state for widget mode)
 *
 */

'use strict';
angular.module('azimutCms.service')

.provider('CmsState', [
'CmsStateDefinition', '$stateProvider', 'MediacenterStateProvider', 'CmsFileDetailSubstatesDefinition',
function (CmsStateDefinition, $stateProvider, MediacenterStateProvider, CmsFileDetailSubstatesDefinition) {
    var provider = this;

    this.attachWidgetSelectFileStatesTo = function(parentStateName) {
        var state = angular.copy(CmsStateDefinition.widgetSelectFile);
        state.name = parentStateName + '.' + state.name;
        $stateProvider.state(state);

        var state = angular.copy(CmsStateDefinition.widgetSelectNewFile);
        state.name = parentStateName + '.' + state.name;
        $stateProvider.state(state);
        MediacenterStateProvider.attachStatesTo(state.name);
    };

    this.attachWidgetFileEditStatesTo = function(parentStateName) {
        var state = angular.copy(CmsStateDefinition.widgetFileEdit);
        state.name = parentStateName + '.' + state.name;
        state.resolve = {
            baseStateName: function() {
                return state.name;
            }
        };
        $stateProvider.state(state);

        // Attach substates
        provider.attachCmsFileDetailSubstatesTo(state.name);
        MediacenterStateProvider.attachStatesTo(state.name);
    };

    this.attachCmsFileDetailSubstatesTo = function(parentStateName) {
        angular.forEach(CmsFileDetailSubstatesDefinition, function(stateDefinition) {
            var state = angular.copy(stateDefinition);
            state.name = parentStateName + '.' + state.name;
            state.resolve = {
                baseStateName: function() {
                    return parentStateName;
                }
            };

            $stateProvider.state(state);
        });
    };

    // Factory service returned after config phase (not used in our case)
    var CmsStateFactory = function() {
    };

    this.$get = function() {
        return CmsStateProvider;
    };
}]);
