/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 12:15:16
 */

'use strict';

angular.module('azimutSecurity.controller')

.controller('SecurityUserListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'SecurityUserFactory', 'DataSortDefinitionBuilder',
function($log, $scope, $state, $stateParams, NotificationService, SecurityUserFactory, DataSortDefinitionBuilder) {
    $log = $log.getInstance('SecurityUserListController');

    $scope.$parent.showContentView = true;

    var users = SecurityUserFactory.users();

    $scope.groupId = $stateParams.groupId;

    // by default, display all users
    $scope.groupUsers = users;

    // apply group filter if a group id is provided
    if('' != $scope.groupId && null != $scope.groupId) {
        $scope.groupUsers = [];
        angular.forEach(users, function(user) {
            angular.forEach(user.groups, function(group) {
                if(group.id == $scope.groupId){
                    $scope.groupUsers.push(user);
                }
            });
        });
    }

    $scope.usersSortDefinitionBuilder = new DataSortDefinitionBuilder('security-users', [
        {
            'label': Translator.trans('first.name'),
            'property': 'firstName',
            'default': true
        },
        {
            'label': Translator.trans('last.name'),
            'property': 'lastName'
        },
        {
            'label': Translator.trans('email'),
            'property': 'email'
        },
        {
            'label': Translator.trans('creation.date'),
            'property': 'id',
            'reverse': true
        }
    ]);

    $scope.openUser = function(user) {
        $state.go('backoffice.security.user_detail',{id:user.id});
    };

    $scope.deleteUser = function(user) {
        SecurityUserFactory.deleteUser(user).then(function (response) {
            $log.info('User has been deleted');
            NotificationService.addSuccess(Translator.trans('notification.success.user.delete'));
        }, function(response) {
            $log.error('Error while deleting user', response);
            NotificationService.addError(Translator.trans('notification.error.user.delete'), response);
        });
    };
}]);
