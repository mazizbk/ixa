/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-09 15:08:38
 */

'use strict';

angular.module('azimutBackoffice.controller')

.controller('BackofficeExternalAppController', [
'$log', '$scope', '$stateParams', 'BackofficeExternalAppFactory', '$state', '$location', '$window', 'MediaDeclinationTagParser', 'baseStateName',
function($log, $scope, $stateParams, BackofficeExternalAppFactory, $state, $location, $window, MediaDeclinationTagParser, baseStateName) {
    $log = $log.getInstance('BackofficeExternalApplicationController');

    // application scope (scope of the main running app), this is required for widgets (sub apps)
    if(undefined == $scope.appScope) {
        $scope.appScope = $scope;
    }

    var appDefinition = BackofficeExternalAppFactory.getAppDefinition($stateParams.appName);

    if (!appDefinition) return;

    $scope.appTitle = appDefinition.menuTitle;
    $scope.appUrl = appDefinition.url + $location.hash();

    var effectiveAppUrl = $scope.appUrl;

    $scope.$on('$locationChangeSuccess', function(event){
        // update app url when location changed
        if (appDefinition.url + $location.hash() != effectiveAppUrl) {
            $scope.appUrl = appDefinition.url + $location.hash();
        }
    });

    $scope.onIframeLoad = function(url, title) {
        // extract app slug
        var urlOffset = url.indexOf(appDefinition.url);
        if (-1 !== urlOffset) {
            effectiveAppUrl = url;
            var slug = url.substr(urlOffset + appDefinition.url.length);
            slug = (0 == slug.indexOf('/')) ? slug.substr(1) : slug;

            $scope.$apply(function() {
                // update url hash fragment
                $location.hash(slug);

                // set main window title
                $scope.setPageTitle(title);
            });
        }
    };

    // expose Mediacenter widget through iframe

    var mediacenterWidgetId = 'externalAppMediaDeclination';

    $scope.appScope.azimutWidgetsParams = {};
    $scope.appScope.azimutWidgetsParams[mediacenterWidgetId] = {
        params: {
            statePrefix: baseStateName,
        },
        callbacks: {
            azimutMediacenterChooseMediaDeclinations: function(mediaDeclinations, options) {
                for(var i = 0, length = mediaDeclinations.length; i < length; i++){
                    mediaDeclinations[i].parsedTag = MediaDeclinationTagParser.tagDefinitionToHtml(mediaDeclinations[i], 'm');
                }
                $('#externalAppIframe')[0].contentWindow.postMessage(mediaDeclinations, '*');
                $('#azimutMediacenterWidget').hide();
            }
        }
    };

    var isMediacenterWidgetLoaded = false;

    $scope.showMediacenterWidget = function() {
        $scope.appScope.widgetId = mediacenterWidgetId;

        if (!isMediacenterWidgetLoaded) $state.go(baseStateName+'.mediacenter', $stateParams);
        isMediacenterWidgetLoaded = true;

        $('#azimutMediacenterWidget').show();
    };

    $scope.setPageTitle(appDefinition.menuTitle);

    $window.addEventListener("message", function(evt) {
        if ('showMediacenterWidget' == evt.data) {
            $scope.showMediacenterWidget();
        }
        else if(evt.data instanceof Object && evt.data.hasOwnProperty('type')) {
            switch(evt.data.type) {
                case "navigateExternalApp":
                    if(!evt.data.hasOwnProperty('app')) {
                        $log.error("Missing property app in posted message from child iframe");

                        return;
                    }
                    var app = BackofficeExternalAppFactory.getAppDefinition(evt.data.app);
                    if(!app) {
                        $log.error("Unknown external app "+evt.data.app);
                        return;
                    }

                    var transition = $state.go('backoffice.external_app', {'appName':app.shortName}).then(function() {
                        for(var i=0; i<$scope.backofficeMenu.length;i++) {
                            var menuItem = $scope.backofficeMenu[i];
                            menuItem.active = menuItem.stateName === 'backoffice.external_app'
                                && menuItem.stateParams instanceof Object
                                && menuItem.stateParams.hasOwnProperty('appName')
                                && menuItem.stateParams.appName === app.shortName
                            ;
                        }
                    });

                    if(evt.data.hasOwnProperty('url')) {
                        transition.then(function() {
                            var url = evt.data.url.replace(app.url, '');
                            $location.hash(url);
                        });
                    }

                    break;
            }
        }
    }, false);

    // Proxy from iframe to MediaDeclinationTagParser
    $window.addEventListener("message", function(evt) {
        if (undefined == evt.data.action) {
            return;
        }

        if ('MediaDeclinationTagParser.htmlTagsToText' == evt.data.action) {
            $('#externalAppIframe')[0].contentWindow.postMessage({
                'action': evt.data.returnAction,
                'elementId': evt.data.elementId,
                'text': MediaDeclinationTagParser.htmlTagsToText(evt.data.text)
            }, '*');
        }

        if ('MediaDeclinationTagParser.tagsInTextToHtml' == evt.data.action) {
            MediaDeclinationTagParser.tagsInTextToHtml(evt.data.text, 'm').then(function(response) {
                $('#externalAppIframe')[0].contentWindow.postMessage({
                    'action': evt.data.returnAction,
                    'elementId': evt.data.elementId,
                    'text': response
                }, '*');
            });
        }
    }, false);
}]);
