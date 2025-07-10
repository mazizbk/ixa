/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 12:22:49
 */

'use strict';

angular.module('azimutFrontofficeSecurity.controller')

.controller('FrontofficeSecurityUserListController', [
'$log', '$scope', '$state', '$stateParams', 'NotificationService', 'FrontofficeSecurityUserFactory', '$window', '$location', 'FrontofficeSiteFactory', 'DataSortDefinitionBuilder',
function($log, $scope, $state, $stateParams, NotificationService, FrontofficeSecurityUserFactory, $window, $location, FrontofficeSiteFactory, DataSortDefinitionBuilder) {
    $log = $log.getInstance('FrontofficeSecurityUserListController');

    $scope.$parent.showContentView = true;

    $scope.users = FrontofficeSecurityUserFactory.users();

    $scope.domainNames = [];

    $scope.usersSortDefinitionBuilder = new DataSortDefinitionBuilder('frontofficeSecurity-users', [
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

    FrontofficeSiteFactory.getSites().then(function (sites) {
        for (var i = sites.length - 1; i >= 0; i--) {
            $scope.domainNames[i] = sites[i].mainDomainName.name;
        }
    });

    $scope.openUser = function(user) {
        $state.go('backoffice.frontofficesecurity.user_detail', {id: user.id});
    };

    $scope.deleteUser = function(user) {
        FrontofficeSecurityUserFactory.deleteUser(user).then(function (response) {
            $log.info('User has been deleted');
            NotificationService.addSuccess(Translator.trans('notification.success.user.delete'));
        }, function(response) {
            $log.error('Error while deleting user', response);
            NotificationService.addError(Translator.trans('notification.error.user.delete'), response);
        });
    };

    $scope.impersonateUserLinks = [];

    $scope.impersonateUser = function(user, domainName) {
        FrontofficeSecurityUserFactory.impersonateUser(user).then(function (response) {
            $log.info('User has been impersonated');
            NotificationService.addSuccess(Translator.trans('notification.success.frontoffice.user.impersonate'), null, {
                sticky: true,
                link: 'http://' + domainName + '/impersonate/' + response.data.token,
                linkLabel: Translator.trans('login.as.%username%', {'username': user.email}),
            });
        }, function(response) {
            $log.error('Error while impersonating user', response);
            NotificationService.addError(Translator.trans('notification.error.frontoffice.user.impersonate'), response);
        });
    };
}]);
