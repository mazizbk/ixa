/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-11-07 14:38:18
 *
 ******************************************************************************
 *
 * Open a yes/no confirmation modal dialog (using ui-bootstrap's $modal)
 * It returns a promise that is resolved or rejected based on confirm/cancel click
 *
 * Usage :
 *
 * azConfirmModal(message,[title, confirmButtonLabel, cancelButtonLabel])
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.service('azConfirmModal', ['$modal', function($modal) {
    return function(message, title, confirmButtonLabel, cancelButtonLabel) {

        confirmButtonLabel = confirmButtonLabel===false ? false : (confirmButtonLabel || Translator.trans('yes'));
        cancelButtonLabel = cancelButtonLabel===false ? false : (cancelButtonLabel || Translator.trans('no'));

        var ModalController = function($scope, $modalInstance, settings) {
            // add settings to scope
            angular.extend($scope, settings);

            $scope.confirm = function() {
                $modalInstance.close(true);
            };

            $scope.cancel = function() {
                $modalInstance.dismiss('cancel');
            };
        };

        // open modal and return the instance (which will resolve the promise on confirm/cancel click)
        var modalInstance = $modal.open({
            controller: ModalController,
            resolve: {
                settings: function() {
                    return {
                        modalTitle: title,
                        modalBody: message,
                        confirmButtonLabel: confirmButtonLabel,
                        cancelButtonLabel: cancelButtonLabel
                    };
                }
            },
            template:   '<div class="dialog-modal"> \
                            <div class="modal-header" ng-show="modalTitle"> \
                                <h3 class="modal-title">{{modalTitle}}</h3> \
                            </div> \
                            <div class="modal-body">{{modalBody}}</div> \
                            <div class="modal-footer"> \
                                <button class="btn btn-success" ng-click="confirm()" ng-show="undefined != confirmButtonLabel">{{confirmButtonLabel}}</button> \
                                <button class="btn btn-warning" ng-click="cancel()" ng-show="undefined != cancelButtonLabel">{{cancelButtonLabel}}</button> \
                            </div> \
                        </div>'
        });

        // return the modal instance
        return modalInstance;
    }
}]);
