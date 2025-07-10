/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-27 17:24:03
 *
 ******************************************************************************
 *
 * /!\ Requires azCollapsable directive
 *
 * Adds a show/hide button to an HTML element with animations
 *
 * Button label can be defined through the optionnal az-collapsable-panel-button-label param
 * Button icon can be defined through the optionnal az-collapsable-panel-button-icon param
 *
 * az-collapsable-panel have an optionnal value that can contain a parameters objet :
 * {
 *     enableStorage: true,
 *     collapsed: true // boolean to trigger collapsing from outside, will be watched if set on scope
 * }
 *
 * Usage : just add az-collapsable-panel attribute to the element you want to wrap.
 *
 *    <div az-collapsable-panel>
 *        my panel content
 *    </div>
 *
 *    <div az-collapsable-panel class="collapsed">
 *        my panel content
 *    </div>
 *
 *    <div az-collapsable-panel="myExternalCollapsingObjectInScope">
 *        my panel content
 *    </div>

 *    <div az-collapsable-panel="{enableStorage: true}">
 *        my panel content
 *    </div>
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azCollapsablePanel', [
'$log',
function($log) {

    $log = $log.getInstance('azCollapsablePanel');

    return {
        restrict: 'A',
        link: function(scope, element, attrs)  {

            var enableStorage = false;
            var params = {};
            var externalCollapseModelGetter, externalCollapseModelSetter;

            // optional external variable for collapsing trigger
            if(undefined != attrs.azCollapsablePanel && '' != attrs.azCollapsablePanel) {


                // detect if the param is a direct JSON object or a name one the scope
                var paramIsDirectObject = false;

                try {
                    // try to convert string into a JSON object
                    params = angular.fromJson(attrs.azCollapsablePanel.replace(/(['"])?([a-zA-Z0-9_]+)(['"])?:/g, '"$2": '));
                    paramIsDirectObject = true;
                }
                catch(e) {
                }

                // params is not a direct object, evaluate it on the scope
                if(!paramIsDirectObject) {

                    params = scope.$eval(attrs.azCollapsablePanel);


                    // optional external variable for collapsing trigger
                    if(undefined != params.collapsed) {

                        scope.$watch(attrs.azCollapsablePanel+'.collapsed', function(newValue) {
                            if(true == newValue) {
                                element.addClass('collapsed');
                                element.find('.collapsable-border-btn').addClass('collapsed');
                            }
                            else {
                                element.removeClass('collapsed');
                                element.find('.collapsable-border-btn').removeClass('collapsed');
                            }
                        });
                    }
                }

                if(undefined != params.enableStorage) {
                    enableStorage = true;
                }
            }

            // disable collapse state storage if an id attribute is not set
            if(enableStorage && !attrs['id']) {
                $log.warn('az-collapsable-panel directive needs an id attribute to be able to store panel state');
                enableStorage = false;
            }

            if(enableStorage) {
                // restore element state
                var storedState = localStorage.getItem('az-collapsable-panel-'+element.attr('id')+'-state');
                if(storedState) {
                    if('true' == storedState) {
                        element.addClass('collapsed');
                        element.find('.collapsable-border-btn').addClass('collapsed');
                    }
                    else {
                        element.removeClass('collapsed');
                        element.find('.collapsable-border-btn').removeClass('collapsed');
                    }
                }
            }

            element.find('.collapsable-border-btn').on('click',function(evt) {
                evt.stopPropagation();

                //in narrow view, close all panel before showing this opened
                if($(window).width() < 768 && element.hasClass('collapsed')) {
                    var panels = $('.collapsable-panel');
                    panels.addClass('transition-active');
                    panels.addClass('collapsed');
                    $('.collapsable-panel .collapsable-border-btn').addClass('collapsed');
                }

                // activate css transition
                element.addClass('transition-active');

                element.toggleClass('collapsed');
                $(this).toggleClass('collapsed');

                if(undefined != params.collapsed) {
                    params.collapsed = element.hasClass('collapsed');
                }



                if(enableStorage) {
                    // store new element width
                    localStorage.setItem('az-collapsable-panel-'+element.attr('id')+'-state', element.hasClass('collapsed')?'true':'false');
                }
            });


        }
    }
}]);



/*
function() {
    return {
        restrict: 'A',
        transclude: true,
        scope: {},
        // use preLink instead of link function because by default the link function of child directive will be ran first
        compile: function compile(element, attrs, transclude) {
            return {
                pre: function preLinkfunction( $scope, element, attrs ) {
                    $scope.collapsed = false;
                    if ("collapsed" == attrs.azCollapsablePanel) {
                        $scope.collapsed = true;
                    }

                    $scope.buttonLabel = 'toggle panel';

                    if(null != attrs.azCollapsablePanelButtonLabel) {
                        $scope.buttonLabel = attrs.azCollapsablePanelButtonLabel;
                    }

                    $scope.buttonIcon = 'toggle panel';

                    if(null != attrs.azCollapsablePanelButtonIcon) {
                        $scope.buttonIcon = 'glyphicon '+attrs.azCollapsablePanelButtonIcon;
                    }
                },
                post: angular.noop
            }
        },
        template:
            '   <div>'+
            '       <div az-collapsable="collapsed" ng-transclude class="azimutBackofficeAppCollapsablePanel"></div>'+
            '       <div class="collapsable-panel-toggler"><a href ng-click="collapsed=!collapsed"><span ng-class="buttonIcon"></span> {{ buttonLabel }}<span ng-class="collapsed && \'caret\' || \'caret caret-inverse\'"></span></a></div>'+
            '   </div>'
    }
});*/

