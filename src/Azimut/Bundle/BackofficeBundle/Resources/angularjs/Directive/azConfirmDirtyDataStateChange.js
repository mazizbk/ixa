/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-02 15:15:56
 *
 ******************************************************************************
 *
 * This directive prevent state change when form on wich it is attached has
 * modified data (dirty data)
 *
 * You can optionnaly set the confirmation message in a scope attribute passed
 * as argument.
 *
 * An optionnal attribute can be added to set the ignored substate. For instance,
 * if it is defined to 'backoffice.cms.file_detail', then all its substate won't
 * trigger the confirmation modal. This is usefull for app widgets (ex: the
 * mediacenter widget in a cmsfile ('backoffice.cms.file_detail.mediacenter.*').
 *
 * Usages :
 *     <form .... az-confirm-dirty-data-state-change>
 *     <form .... az-confirm-dirty-data-state-change="forms.params.cms_file.confirmDirtyDataStateChangeMessage" az-confirm-dirty-data-state-change-ignore-substates-of="forms.params.cms_file.confirmDirtyDataStateChangeIgnoreSubstatesOf">
 *
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azConfirmDirtyDataStateChange', [
'azConfirmModal', '$state', '$log',
function(azConfirmModal, $state, $log) {
    $log = $log.getInstance('azConfirmDirtyDataStateChange');

    return {
        restrict: 'A',
        require: 'form',
        link: function(scope, element, attrs, formController) {
            scope.$on("$stateChangeStart",function(event, toState, toParams, fromState, fromParams, options) {
                var ignoreSubstatesOf = scope.$eval(attrs.azConfirmDirtyDataStateChangeIgnoreSubstatesOf);

                if (undefined == ignoreSubstatesOf || -1 == toState.name.indexOf(ignoreSubstatesOf)) {
                    if ((-1 == toState.name.indexOf($state.$current.name) || (toState.name == $state.$current.name && !angular.equals(toParams, fromParams))) && formController.$dirty) {
                        // prevent state change
                        event.preventDefault();

                        if(undefined != scope.backofficeAppStatus) {
                            scope.backofficeAppStatus.loading = false;
                        }

                        azConfirmModal(scope.$eval(attrs.azConfirmDirtyDataStateChange) || Translator.trans('dirty.data.on.state.are.you.sure.you.want.to.leave')).result.then(function(result) {
                            if (result) {
                                if(undefined != scope.backofficeAppStatus) {
                                    scope.backofficeAppStatus.loading = true;
                                }
                                // remove dirty state on form
                                formController.$setPristine();
                                $state.go(toState, toParams);
                            }
                        });
                    }
                }
            });
        }
    };
}]);
