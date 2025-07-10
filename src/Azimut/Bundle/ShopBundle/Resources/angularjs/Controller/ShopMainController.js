/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:41:28
 */

'use strict';

angular.module('azimutShop.controller')

.controller('ShopMainController',[
'$log', '$scope','$state', 'NotificationService', 'ShopOrderFactory',
function($log, $scope, $state, NotificationService, ShopOrderFactory) {
    $log = $log.getInstance('ShopMainController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    // if(!ShopOrderFactory.isGrantedUser()) {
    //     $log.warn("User has not access to ShopOrderFactory data");
    //     $state.go('backoffice.forbidden_app', {appName: 'shop'});
    //     return;
    // }

    $scope.Translator = Translator;

    $scope.setPageTitle(Translator.trans('shop.meta.title'));

    $scope.NotificationService = NotificationService;
    $scope.Translator = Translator;

    // clear notification at each state change
    $scope.$on('$stateChangeStart', function(evt){
        NotificationService.clear();
    });

    // show loader in main content panel
    // set this to true if the content is loaded from this controller
    // leave it to false if the content was preloader before calling controller
    $scope.isMainContentLoading = false;

    // set function to allow easy update of loading state in children scopes
    $scope.mainContentLoading = function() {
        $scope.isMainContentLoading = true;
    }
    $scope.mainContentLoaded = function() {
        $scope.isMainContentLoading = false;
    }

    // retrieve data from your entity factory like for example:
    //ex: $scope.files = FileFactory.files();

    // when an error occurs, set this to false to hide the content of the main panel
    $scope.showContentView = true;

    // at startup, go to default substate
    if($state.current.name == 'backoffice.shop') $state.go('backoffice.shop.order_list');
}]);
