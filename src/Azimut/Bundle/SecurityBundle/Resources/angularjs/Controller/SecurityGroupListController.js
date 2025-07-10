/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-06-13 12:00:52
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityGroupListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'SecurityGroupFactory', 'DataSortDefinitionBuilder',
function($log, $scope, $state, $stateParams, NotificationService, SecurityGroupFactory, DataSortDefinitionBuilder) {
    $log = $log.getInstance('SecurityGroupListController');

    $scope.$parent.showContentView = true;

    $scope.groupsSortDefinitionBuilder = new DataSortDefinitionBuilder('security-groups', [
        {
            'label': Translator.trans('name'),
            'property': 'name',
            'default': true
        },
        {
            'label': Translator.trans('creation.date'),
            'property': 'id',
            'reverse': true
        }
    ]);

    $scope.openGroup = function(group) {
        $state.go('backoffice.security.group_detail', {id:group.id});
    };

    $scope.deleteGroup = function(group) {
        SecurityGroupFactory.deleteGroup(group).then(function (response) {
            $log.info('Group has been deleted');
            NotificationService.addSuccess(Translator.trans('notification.success.group.delete'));
        }, function(response) {
            $log.error('Error while deleting group', response);
            NotificationService.addError(Translator.trans('notification.error.group.delete'), response);
        });
    };
}]);
