/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 14:22:12
 *
 ******************************************************************************
 *
 * initialise tiny mce based on data-tinymce-config attribute
 * data-tinymce-config must contain tiny mce config objet in json (ex: {"theme":"azimut"})
 * this is made for tiny mce 4
 *
 * Usage :
 *     <textarea
 *         ng-model="forms.data.cms_file['cmsFileType']['text']"
 *         id="cms_file_cmsFileType_text"
 *         name="cms_file[cmsFileType][text]"
 *         az-tinymce
 *         az-tinymce-mediacenter-widget-params="forms.widgets.cms_file_cmsFileType_text"
 *         data-tinymce-config="json_tinymce_config"
 *     ></textarea>
 *
 * As extra parameters, we can activate widgets inside tinymce (like the mediacenter plugin).
 * Example :
 *     <textarea
 *         ...
 *         az-tinymce
 *         az-tinymce-mediacenter-widget-params="forms.widgets.cms_file_cmsFileType_text"
 *         ...
 *     ></textarea>
 *
 *     This will try to fetch forms.widgets.cms_file_cmsFileType_text object on the scope, if not
 *     defined, it will fetch forms.widgets.default_cms_file_az_tinymce on the scope instead.
 *
 *     To trigger mediacenter widget from angular controller :
 *
 *      forms.widgets.default_cms_file_az_tinymce = {
 *           //action that will be triggered on widget button click
 *           onShow: function() {
 *               //activate mediacenter widget when shown for the first time
 *               if (!$state.includes(baseStateName + '.mediacenter')) $state.go(baseStateName + '.mediacenter', $stateParams);
 *           },
 *           containerId: 'azimutMediacenterWidget',
 *           //callback function name for widget
 *           callbackName: 'azimutMediacenterChooseMediaDeclinations',
 *           params: {
 *               statePrefix: $state.current.name
 *           }
 *       };
 */

'use strict';

angular.module('azimutBackoffice.directive')

.directive('azTinymce',[
'MediaDeclinationTagParser', '$timeout', '$log',
function(MediaDeclinationTagParser, $timeout, $log) {
    $log = $log.getInstance('azTinymce');

    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            if(undefined == attrs.tinymceConfig) return;

            var tinymceConfig = angular.fromJson(attrs.tinymceConfig);

            // TODO: refactor this with azFormEntityJs

            var widgetId = attrs['id'];
            var widgetParamsObjName = attrs['azTinymceMediacenterWidgetParams'];
            var widgetParams;
            var mediacenterWidgetActive = false;
            var widgetModal = null;

            var startTinyMce = function() {

                tinymceConfig.setup = function(editor) {

                    // update model on editor content change
                    editor.on('change', function(e) {
                        scope.$apply(function() {
                            ngModel.$setViewValue(editor.getContent());
                        });
                    });

                    // plug mediacenter widget if enabled
                    if(mediacenterWidgetActive) {

                        if(undefined == scope.appScope) {
                            $log.error('Mediacenter widget in tinymce activated but scope.appScope not defined');
                            return;
                        }

                        // plug callback function
                        if(undefined == scope.appScope.azimutWidgetsParams[widgetId].callbacks) scope.appScope.azimutWidgetsParams[widgetId].callbacks = [];
                        scope.appScope.azimutWidgetsParams[widgetId].callbacks[widgetParams.callbackName] = function(results,options) {

                            editor.focus();
                            editor.selection.setContent(MediaDeclinationTagParser.tagDefinitionToHtml(results[0], 'm'));

                            // update editor content model
                            ngModel.$setViewValue(editor.getContent());

                            //close modal
                            if(widgetModal) {
                                widgetModal.modal('hide');
                            }

                            //if a DOM container has been specified, hide it
                            if(null != widgetParams.containerId) {
                                $('#'+widgetParams.containerId).hide();
                            }

                            if(null != widgetParams.onSet) widgetParams.onSet(results[0].id);

                            //TODO : handle multiple results

                        }

                        editor.addButton('mediacenter', {
                            title : 'Mediacenter',
                            icon : 'mediacenter',
                            //text: 'Mediacenter',
                            onclick : function() {
                                //set the widgetId on the application scope so the widget can access it even if not a direct child
                                scope.appScope.widgetId = widgetId;

                                if(widgetModal) {
                                    widgetModal.modal('show');
                                }

                                //if a DOM container has to be shown
                                if(null != widgetParams.containerId) {
                                    $('#'+widgetParams.containerId).show();
                                }
                                if(null != widgetParams.onShow) widgetParams.onShow();

                                return false;
                            }
                        });

                        editor.on('SaveContent', function(evt) {
                            editor.setContent(MediaDeclinationTagParser.htmlTagsToText(editor.getContent()));

                            // force model update
                            scope.$apply(function() {
                                ngModel.$setViewValue(editor.getContent());
                            });
                        });

                        editor.on('init', function() {
                            // transform media declination on first model set value

                            // if model already set, launch transformation
                            if (null != ngModel.$viewValue) {
                                MediaDeclinationTagParser.tagsInTextToHtml(ngModel.$viewValue, 'm').then(function(parsedValue) {
                                    if(ngModel.$viewValue != parsedValue) {
                                        ngModel.$setViewValue(parsedValue);
                                        editor.setContent(parsedValue);
                                    }
                                });
                            }
                            // in case model not set, we'll transform it on the next value change
                            else {
                                var unsetNgModelWatcher = scope.$watch(attrs.ngModel, function(newValue) {
                                    if(undefined != newValue) {
                                        // TODO: proceed to tag parsing on the server side to limit overhead !
                                        MediaDeclinationTagParser.tagsInTextToHtml(newValue, 'm').then(function(parsedValue) {
                                            if(newValue != parsedValue) {
                                                ngModel.$setViewValue(parsedValue);
                                                editor.setContent(parsedValue);
                                            }
                                        });

                                        // kill watcher
                                        unsetNgModelWatcher();
                                    }
                                });
                            }
                        });
                    }
                }

                tinymceConfig.target = element[0];
                tinymce.remove('#' + element[0].id);

                tinymce.init(tinymceConfig);
            }

            // if widget active, plug it
            if(null != widgetId && null != widgetParamsObjName) {
                var isPluggedWidgetObjParams = false;

                var azimutWidgetsParamsDebugHelper = $timeout(function() {
                    $log.warn('azTinyMCE with mediacenter widget not initialized after 4sec. Maybe "'+ widgetParamsObjName +'" and "forms.widgets.default_cms_file_az_tinymce" are not defined.');
                    // Start TinyMCE without mediacenter widget
                    startTinyMce();
                }, 4000);

                function plugWidgetParam(newWidgetParams) {
                    $timeout.cancel(azimutWidgetsParamsDebugHelper);

                    mediacenterWidgetActive = true;

                    widgetParams = newWidgetParams;

                    // NestedModelInstanciator.instanciate(attrs.ngModel, scope);

                    var widgetModal = null;

                    // if a modal has to be shown, first retrieve the modal dom object
                    if(null != widgetParams.modalId) {
                        widgetModal = $('#'+widgetParams.modalId);
                    }

                    // if a modal has to be shown, first retrieve the modal dom object
                    if(null != widgetParams.modalId) {
                        widgetModal = $('#'+widgetParams.modalId);
                    }

                    // set namespace for widget params
                    if(undefined == scope.appScope.azimutWidgetsParams) scope.appScope.azimutWidgetsParams = {};
                    if(undefined == scope.appScope.azimutWidgetsParams[widgetId]) scope.appScope.azimutWidgetsParams[widgetId] = {};

                    // register params for widget
                    scope.appScope.azimutWidgetsParams[widgetId].params = widgetParams.params;

                    startTinyMce();
                }

                // retrieve param object
                var unsetWidgetWatcher = scope.$watch(widgetParamsObjName, function(newWidgetParams) {
                    if(!newWidgetParams) return;

                    plugWidgetParam(newWidgetParams);

                    isPluggedWidgetObjParams = true;

                    // kill watcher
                    unsetWidgetWatcher();
                });

                // retrieve default param object
                var unsetDefaultWidgetWatcher = scope.$watch('forms.widgets.default_cms_file_az_tinymce', function(newDefaultWidgetParams) {
                    if(!newDefaultWidgetParams) return;

                    if (!isPluggedWidgetObjParams) {
                        $log.info('azTinyMCE with mediacenter widget initialized with default config');
                        plugWidgetParam(newDefaultWidgetParams);
                    }

                    // kill watcher
                    unsetDefaultWidgetWatcher();
                });

            }
            else {
                // Start TinyMCE without mediacenter widget
                startTinyMce();
            }
        }
    }
}]);
